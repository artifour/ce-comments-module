<?php

namespace ProjectName\Comment\Application\Guard;

use Core\Application\Builder\UrlBuilder;
use Core\Domain\Service\UserServiceInterface;
use ProjectName\Comment\Application\Exception\DuplicateCommentException;
use ProjectName\Comment\Application\Exception\SendingCommentsTooOftenException;
use ProjectName\Comment\Application\Exception\SpamCommentException;
use ProjectName\Comment\Application\ValueObject\CommentSpamValueThreshold;
use ProjectName\Comment\Domain\Entity\Comment;
use ProjectName\Comment\Domain\Repository\CommentRepositoryInterface;
use ProjectName\Comment\Domain\ValueObject\CommentStatus;

class CommentSpamGuard
{
    private const CYRILLIC_PATTERN = '/[а-яё]+/iu';

    private const ALLOWED_HOSTS_PATTERNS = ['/^deti123\.ru/i'];
    private const BANNED_HOSTS_PATTERNS = ['/porn/i', '/crypt/i', '/sex/i', '/onion/i'];

    private const LONG_NUMBER_PATTERN = '/\d{5,}/';

    private const SUSPICIOUS_WORDS = [
        // прочее
        'онлайн', 'портал', 'казино', 'реклама', 'производ', 'крипт', 'скачать', 'монтаж', 'сайт', 'ссылк',
        // ругательства и нежелательные комментарии
        'сук', 'говно', 'хуй', 'жопа', 'епт', 'гамно', 'нах', 'чушь', 'чмо', 'ужас', 'пидар', 'бред', 'фигня',
        'хрен', 'лох', 'хуе', 'дура', 'анал', 'тупо', 'пидр', 'ебл', 'гей', 'мерз', 'ахуи', 'ахуе',
        // финансы/услуги
        'успех', 'зарабат', 'деньги', 'доход', 'финанс', 'богат', 'букмекер', 'продаж', 'бизнес', 'продвиж', 'робот',
        'money', 'коммерч', 'алкогол', 'купит', 'юридич', 'партнер', 'компан', 'покуп', 'диллер', 'магаз', 'заказ',
        'валют', 'адвокат', 'ставк', 'ставок', 'услу',
        // порнуха
        'free', 'vids', 'pics', 'galler', 'movie', 'sex', 'nude', 'dating', 'tinyurl', 'секс', 'trap', 'порно',
    ];
    private const BANNED_WORDS = [
        // прочее
        'xbet', 'casino',
        // порнуха
        'porn', 'gay', 'shemale', 'hentai', 'порно', 'drugs', 'pussy', 'account',
    ];

    /** @var UserServiceInterface $userService */
    private $userService;

    /** @var CommentRepositoryInterface */
    private $commentRepository;

    public function __construct(UserServiceInterface $userService, CommentRepositoryInterface $commentRepository)
    {
        $this->userService = $userService;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @param Comment $comment
     *
     * @throws SpamCommentException
     * @throws DuplicateCommentException
     * @throws SendingCommentsTooOftenException
     */
    public function guard(Comment $comment): void
    {
        if ($this->userService->isAdministrator()) {
            return;
        }

        $spamValue = $this->estimateValue($comment);

        if ($this->userService->isGuest()) {
            if ($spamValue >= CommentSpamValueThreshold::TOTALLY_SPAM) {
                throw new SpamCommentException();
            }

            if ($spamValue >= CommentSpamValueThreshold::SUSPICIOUS) {
                $comment->status = CommentStatus::SUSPICIOUS;
            } else {
                $comment->status = CommentStatus::WAITING_MODERATION;
            }
        } elseif ($spamValue >= CommentSpamValueThreshold::LIKELY_SPAM) {
            $comment->status = CommentStatus::WAITING_MODERATION;
        } else {
            $comment->status = CommentStatus::ACTIVE;
        }

        $this->checkDuplicatesAndFrequency($comment);
    }

    /**
     * @param Comment $comment
     *
     * @return float
     */
    private function estimateValue(Comment $comment): float
    {
        $value = 0;

        if ($comment->name === $comment->content) {
            $value += 100;
        }

        /* Считаем, что на русском детском сайте вероятность комментария на иностранном языке крайне мала.
         * Исключением может быть комментарий состоящий из смайликов.
         */
        if (!$this->containsCyrillic($comment->content) && !$this->containsSmilesOnly($comment->content)) {
            $value += 200;
        }

        /* Ссылки на другие сайты в принципе неприемлемы.
         */
        if ($this->containsNotAllowedUrls($comment->content, $externalUrls, $bannedUrls)) {
            $value += 100 * $externalUrls + 500 * $bannedUrls;
        }

        if ($value >= 1000) {
            return 1;
        }

        /* Т. к. мы просто ищем вхождение в строку по отрывку, то не можем гарантировать, что это именно то слово,
         * которое мы ищем.
         * Например, нельзя добавить "сук" и ожидать, что будут найдены только лишь комментарии с ругательствами.
         * Гарантию попадания комментария в категорию спама обеспечиваем количеством таких паттернов.
         */
        $suspiciousWordsCount = $this->getSuspiciousWordsContainsCount($comment->content);
        if ($suspiciousWordsCount > 0) {
            $value += 100 * $suspiciousWordsCount;
        }

        if ($value >= 1000) {
            return 1;
        }

        $bannedWordsCount = $this->getBannedWordsContainsCount($comment->content);
        if ($bannedWordsCount > 0) {
            $value += 500 * $bannedWordsCount;
        }

        if ($value >= 1000) {
            return 1;
        }

        if ($comment->name) {
            $suspiciousWordsCount = $this->getSuspiciousWordsContainsCount($comment->name);
            if ($suspiciousWordsCount > 0) {
                $value += 100 * $suspiciousWordsCount;
            }

            $bannedWordsCount = $this->getBannedWordsContainsCount($comment->name);
            if ($bannedWordsCount > 0) {
                $value += 500 * $bannedWordsCount;
            }
        }

        /* Встречаются сообщения только лишь с номером и женским именем - шлюхи.
         * Да и откуда в сообщении под сказкой взяться длинной цифре?
         */
        if ($this->containsLongNumbersOrPhoneNumbers($comment->content)) {
            $value += 200;
        }

        return min(1000, $value)/1000;
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    private function containsCyrillic(string $message): bool
    {
        return (bool)preg_match(self::CYRILLIC_PATTERN, $message);
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    private function containsSmilesOnly(string $message): bool
    {
        $count = preg_match_all(
            '/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u',
            $message
        );
        return mb_strlen($message) - $count * 2 < 5;
    }

    /**
     * @param string $message
     * @param int|null $externalUrls
     * @param int|null $bannedUrls
     *
     * @return bool
     */
    private function containsNotAllowedUrls(string $message, ?int &$externalUrls, ?int &$bannedUrls): bool
    {
        $externalUrls = 0;
        $bannedUrls = 0;
        if (preg_match_all(UrlBuilder::URL_PATTERN, $message, $matches) > 0) {
            foreach ($matches[1] as $url) {

                foreach (self::BANNED_HOSTS_PATTERNS as $bannedHostPattern) {
                    if (preg_match($bannedHostPattern, $url) !== false) {
                        $bannedUrls++;
                        continue 2;
                    }
                }

                foreach (self::ALLOWED_HOSTS_PATTERNS as $allowedHostPattern) {
                    if (preg_match($allowedHostPattern, $url) !== false) {
                        continue 2;
                    }
                }

                $externalUrls++;
            }
        }

        return ($externalUrls > 0) || ($bannedUrls > 0);
    }

    /**
     * @param string $message
     *
     * @return int
     */
    private function getSuspiciousWordsContainsCount(string $message): int
    {
        $count = 0;
        foreach (self::SUSPICIOUS_WORDS as $word) {
            if (stripos($message, $word) !== false) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param string $message
     *
     * @return int
     */
    private function getBannedWordsContainsCount(string $message): int
    {
        $count = 0;
        foreach (self::BANNED_WORDS as $word) {
            if (stripos($message, $word) !== false) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    private function containsLongNumbersOrPhoneNumbers(string $message): bool
    {
        return preg_match(self::LONG_NUMBER_PATTERN, $message) > 0;
    }

    /**
     * @param Comment $comment
     *
     * @throws DuplicateCommentException
     * @throws SendingCommentsTooOftenException
     */
    private function checkDuplicatesAndFrequency(Comment $comment): void
    {
        if (!$comment->ipAddress) {
            return;
        }

        $lastComment = $this->commentRepository->findLastOneByIpAddress($comment->ipAddress);
        if (!$lastComment) {
            return;
        }

        if ($lastComment->content === $comment->content) {
            throw new DuplicateCommentException();
        }

        if ($comment->createdAt - $lastComment->createdAt < 2) {
            throw new SendingCommentsTooOftenException();
        }
    }
}
