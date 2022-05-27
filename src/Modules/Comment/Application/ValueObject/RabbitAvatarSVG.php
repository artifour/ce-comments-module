<?php

namespace Deti123\Comment\Application\ValueObject;

class RabbitAvatarSVG
{
    private $md5;
    
    public function __construct(string $hash)
    {
        $this->md5 = array_map(
            static function (string $hex): float {
                return hexdec($hex) / 15;
            },
            str_split($hash)
        );
    }

    public function __toString()
    {
        $headTopY = 45 + 5 * $this->md5[0];
        $headStartX = 30 + 10 * $this->md5[1];
        $headEndX = 70 - 10 * $this->md5[2];
        $headHeight = 120 + 30 * $this->md5[3];

        $earTopY = 5 + 15 * $this->md5[4];

        $leftEarX = 40 - 20 * $this->md5[5];
        $leftEarWidth = 4 + 2 * $this->md5[9];
        $leftEarQX = $leftEarX - $leftEarWidth;

        $rightEarX = 60 + 20 * $this->md5[6];
        $rightEarWidth = 4 + 2 * $this->md5[10];
        $rightEarQX = $rightEarX + $rightEarWidth;

        $skinColorRed = 245 - 35 * $this->md5[7];
        $skinColorBlue = $skinColorRed + 10 - 20 * $this->md5[8];
        $skinColor = '#' . dechex($skinColorRed) . dechex(min($skinColorRed, $skinColorBlue)) . dechex($skinColorBlue);

        $eyesTopY = $headTopY + 16 + 5 * $this->md5[11];
        $eyesCenterX = 49 + 2 * $this->md5[12];
        $eyesRotate = 12 + 3 * $this->md5[19];
        $eyebrowsTopY = $eyesTopY - 5 + 1 * $this->md5[17];
        $eyebrowsTopLeftY = $eyebrowsTopY - 3;
        $eyebrowsTopRightY = $eyebrowsTopY - 3;

        $noseLeftX = 47 + 2 * $this->md5[13];
        $noseTopY = $eyesTopY + 3 + 5 * $this->md5[14];
        $noseWidth = 2 + 2 * $this->md5[15];
        $noseHeight = 3 + 2 * $this->md5[16];
        $noseRightX = $noseLeftX + $noseWidth;
        $noseBottomX = $noseLeftX + $noseWidth/2;
        $noseBottomY = $noseTopY + $noseHeight;

        $smileTopY = $noseBottomY + 2 + 1 * $this->md5[20];
        $smileBottomY = $smileTopY + 3;

        $lineTopY = $noseTopY + 1;
        $lineBottomY = $smileTopY + 1;

        $svg =  "<svg xmlns='http://www.w3.org/2000/svg' height='100px' width='100px'><defs><g id='r'><path d='M40,80Q{$leftEarQX},{$earTopY} {$leftEarX},{$earTopY}T45,80' fill='#fff'/><path d='M60,80Q{$rightEarQX},{$earTopY} {$rightEarX},{$earTopY}T55,80' fill='#fff'/><path d='M{$headStartX},{$headHeight}Q20,{$headTopY} 50,{$headTopY}T{$headEndX},{$headHeight}' fill='#fff'/></g><mask id='m'><use href='#r'/></mask></defs><use href='#r' stroke='#000' stroke-width='1.5'/><rect fill='{$skinColor}' width='100' height='100' mask='url(#m)'/><path d='M40,{$eyebrowsTopY} Q41,{$eyebrowsTopLeftY} 43,{$eyebrowsTopY}' fill='none' stroke='black' stroke-width='0.5'/><ellipse rx='1' ry='2' cx='40' cy='{$eyesTopY}' transform='rotate({$eyesRotate} 45 {$eyesTopY})'/><path d='M58,{$eyebrowsTopY} Q59,{$eyebrowsTopRightY} 61,{$eyebrowsTopY}' fill='none' stroke='black' stroke-width='0.5'/><ellipse rx='1' ry='2' cx='60' cy='{$eyesTopY}' transform='rotate({-$eyesRotate} 55 {$eyesTopY})'/><polygon points='{$noseLeftX},{$noseTopY} {$noseBottomX},{$noseBottomY} {$noseRightX},{$noseTopY}' stroke-width='0.5'/><line x1='{$noseBottomX}' y1='{$lineTopY}' x2='50' y2='{$lineBottomY}' stroke='black' stroke-width='0.5'/><path d='M47,{$smileTopY} Q50,{$smileBottomY} 53,{$smileTopY}' fill='none' stroke='black' stroke-width='0.8'/></svg>";

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }
}
