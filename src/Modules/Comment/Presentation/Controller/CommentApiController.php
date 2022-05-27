<?php

namespace ProjectName\Comment\Presentation\Controller;

use Core\Application\Assembler\PaginationQueryAssemblerInterface;
use Core\Infrastructure\Controller\BaseApiController;
use ProjectName\Comment\Application\Command\ApproveCommentCommand;
use ProjectName\Comment\Application\Service\CreateCommentService;
use ProjectName\Comment\Application\Command\DeleteCommentCommand;
use ProjectName\Comment\Application\Service\GetCommentsService;
use ProjectName\Comment\Application\Command\UpdateCommentContentCommand;
use ProjectName\Comment\Presentation\Form\CreateCommentForm;
use ProjectName\Comment\Presentation\Form\GetCommentsForm;
use ProjectName\Comment\Presentation\Widget\CommentListWidget;
use ProjectName\Toastr\Application\Builder\ToastrResponseBuilder;

class CommentApiController extends BaseApiController
{
    /** @var GetCommentsService */
    private $getCommentsService;

    /** @var ApproveCommentCommand */
    private $approveCommentCommand;

    /** @var DeleteCommentCommand */
    private $deleteCommentCommand;

    /** @var CreateCommentService */
    private $createCommentService;

    /** @var UpdateCommentContentCommand */
    private $updateCommentContentCommand;

    /** @var PaginationQueryAssemblerInterface */
    private $paginationQueryAssembler;

    public function __construct(
        $id,
        $module,

        GetCommentsService $getCommentsService,
        ApproveCommentCommand $approveCommentCommand,
        DeleteCommentCommand $deleteCommentCommand,
        CreateCommentService $createCommentService,
        UpdateCommentContentCommand $updateCommentContentCommand,
        PaginationQueryAssemblerInterface $paginationQueryAssembler,

        $config = []
    ) {
        $this->getCommentsService = $getCommentsService;
        $this->approveCommentCommand = $approveCommentCommand;
        $this->deleteCommentCommand = $deleteCommentCommand;
        $this->createCommentService = $createCommentService;
        $this->updateCommentContentCommand = $updateCommentContentCommand;
        $this->paginationQueryAssembler = $paginationQueryAssembler;

        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritDoc
     */
    protected function verbs(): array
    {
        return [
            'create' => ['PUT'],
            'load' => ['GET'],
            'delete' => ['DELETE'],
            'update' => ['POST'],
            'approve' => ['PATCH'],
        ];
    }

    /**
     * PUT api/comment
     */
    public function actionCreate()
    {
        $createCommentForm = new CreateCommentForm();
        if (!$this->loadAndValidateForm($createCommentForm)) {
            return $this->statusValidationErrors($createCommentForm);
        }

        $comment = $this->createCommentService->create(
            $createCommentForm->getEntity(),
            $createCommentForm->getEntityId(),
            $createCommentForm->getComment(),
            $createCommentForm->getParentId()
        );

        return $this->statusCreated([
            'content' => CommentListWidget::widget(['comments' => $comment]),
            'toastr' => (new ToastrResponseBuilder())->addSuccess('Благодарим Вас за комментарий, он появится после модерации'),
        ]);
    }

    /**
     * GET api/comment
     */
    public function actionLoad()
    {
        $getCommentsForm = new GetCommentsForm();
        $params = $this->getRequestParams();
        if (!$this->loadAndValidateForm($getCommentsForm, $params)) {
            return $this->statusValidationErrors($getCommentsForm);
        }

        $pagination = $this->paginationQueryAssembler->assembleByParams($params);

        $comments = $this->getCommentsService->execute(
            $getCommentsForm->getEntity(),
            $getCommentsForm->getEntityId(),
            $getCommentsForm->getParentId(),
            $getCommentsForm->getFilter(),
            $pagination
        );

        if ($comments) {
            return $this->statusOK([
                'content' => CommentListWidget::widget([
                    'comments' => $comments,
                    'pagination' => $pagination,
                ]),
            ]);
        }

        return $this->statusNoContent();
    }

    /**
     * PATCH api/comment/{id}
     */
    public function actionApprove(int $id)
    {
        if (!$this->approveCommentCommand->execute($id)) {
            return $this->statusNotFound();
        }
    }

    /**
     * DELETE api/comment/{id}
     */
    public function actionDelete(int $id)
    {
        if (!$this->deleteCommentCommand->execute($id)) {
            return $this->statusNotFound();
        }
    }

    /**
     * POST api/comment/{id}
     */
    public function actionUpdate(int $id)
    {
        $data = $this->getPostJson();

        if (!$this->updateCommentContentCommand->execute($id, $data['content'])) {
            return $this->statusNotFound();
        }
    }
}
