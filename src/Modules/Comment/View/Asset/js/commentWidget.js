const commentWidget = {
    _paddingPostId: 0,

    _resizeTextarea: function (elem) {
        let offset = elem.offsetHeight - elem.clientHeight;
        $(elem)
            .height(0)
            .height(elem.scrollHeight - offset - parseInt($(elem).css('padding-top')))
            .removeClass('is-invalid')
            .parent()
            .next('.invalid-feedback')
            .hide();
    },

    init: function (commentsListWrapper) {
        const entity = commentsListWrapper.data('entity');
        const entityId = commentsListWrapper.data('entityId');

        const username = localStorage.getItem('comment-username');
        if (username) {
            commentsListWrapper.find('input[name=username]').val(username);
        }

        commentsListWrapper.find('.comment-form-container textarea').on('input', function () {
            commentWidget._resizeTextarea(this);
        }).each(function () {
            commentWidget._resizeTextarea(this);
        });

        commentApi.load(entity, entityId, null, null).done(function (response, textStatus, jqXHR) {
            if (jqXHR.status !== 200) {
                commentsListWrapper.children('.comments-list').html('');
                return;
            }

            commentsListWrapper.children('.comments-list').html(response.content);
        });
    },

    loadMore: function (elem) {
        const paginationButton = $(elem);
        const commentLoadMore = paginationButton.hide().closest('.comment-load-more');
        commentLoadMore.children('.loaded').show();
        const commentsListWrapper = paginationButton.closest('.comments-list-wrapper');
        const entity = commentsListWrapper.data('entity');
        const entityId = commentsListWrapper.data('entityId');
        const page = paginationButton.data('page');

        commentApi.load(entity, entityId, null, null, page).done(function (response, textStatus, jqXHR) {
            if (jqXHR.status !== 200) {
                commentLoadMore.replaceWith('');
                return;
            }

            commentLoadMore.replaceWith(response.content);
        });
    },

    /**
     * @private
     */
    _showLoading: function (commentsListWrapper) {
        commentsListWrapper.children('.comments-list').html(`
            <div id="page-loading" class="loading text-center">
                <span><i class="far fa-spinner fa-spin fa-2x"></i></span>
            </div>
        `);
    },

    loadByFilter: function (elem, filter) {
        const commentsListWrapper = $(elem).closest('.comments-list-wrapper');
        const entity = commentsListWrapper.data('entity');
        const entityId = commentsListWrapper.data('entityId');

        commentWidget._showLoading(commentsListWrapper);

        commentApi.load(entity, entityId, filter, null).done(function (response, textStatus, jqXHR) {
            if (jqXHR.status !== 200) {
                commentsListWrapper.children('.comments-list').html('');
                return;
            }

            commentsListWrapper.children('.comments-list').html(response.content);
        });
    },

    send: function (elem) {
        const sendButton = $(elem).prop('disabled', true);
        const commentFormContainer = sendButton.closest('.comment-form-container');
        const commentsListWrapper = commentFormContainer.closest('.comments-list-wrapper');
        const commentsList = commentsListWrapper.find('.comments-list');
        const entity = commentsListWrapper.data('entity');
        const entityId = commentsListWrapper.data('entityId');

        const usernameInput = commentFormContainer.find('input[name=username]');
        let username = null;
        if (usernameInput.length) {
            username = commentWidget.getAndValidateUsername(usernameInput);
            if (!username) {
                sendButton.prop('disabled', false);
                return;
            }
        }

        const contentTextarea = commentFormContainer.find('textarea[name=content]');
        const content = commentWidget.getAndValidateContent(contentTextarea);
        if (!content) {
            sendButton.prop('disabled', false);
            return;
        }

        const parentId = commentFormContainer.data('parent-id');
        let postContainer;
        if (parentId) {
            const post = $(`.post[data-id=${parentId}]`);

            postContainer = post.children('.descr').children('.children');
        } else {
            postContainer = commentsList;
        }

        const placeholder = commentsListWrapper
            .children('.placeholder')
            .clone()
            .css('display', 'flex')
            .prependTo(postContainer);

        commentApi
            .create(entity, entityId, parentId, content, username, null)
            .done(function (response) {
                contentTextarea.val('');
                postContainer.prepend(response.content);
                toastrResponse.read(response);
            }).fail(function (jqXHR) {
                if (jqXHR.status === 400) {
                    const response = jqXHR.responseJSON;
                    if (response.field === 'username') {
                        commentWidget.invalidateUsernameInput(usernameInput);
                    } else if (response.field === 'content') {
                        commentWidget.invalidateContentTextarea(contentTextarea, response.message);
                    }
                }
            }).always(function () {
                sendButton.prop('disabled', false);
                placeholder.remove();
            });
    },

    /**
     * @param {jQuery} usernameInput
     * @returns {string|null}
     */
    getAndValidateUsername: function(usernameInput) {
        const username = usernameInput.val().trim();
        if (!commentUsernameRegExp.test(username)) {
            commentWidget.invalidateUsernameInput(usernameInput);

            return null;
        }

        return username;
    },

    /**
     * @param {jQuery} usernameInput
     */
    invalidateUsernameInput: function(usernameInput) {
        usernameInput.addClass('is-invalid');
    },

    /**
     * @param {jQuery} contentTextarea
     * @returns {string|null}
     */
    getAndValidateContent: function(contentTextarea) {
        const content = contentTextarea.val().trim();
        if (!content) {
            commentWidget.invalidateContentTextarea(contentTextarea, commentErrorMessageContentCannotBeEmpty);

            return null;
        }

        return content;
    },

    /**
     * @param {jQuery} contentTextarea
     * @param {string} errorMessage
     */
    invalidateContentTextarea: function(contentTextarea, errorMessage) {
        contentTextarea
            .addClass('is-invalid')
            .parent()
            .next('.invalid-feedback')
            .html(errorMessage)
            .show();
    },

    approve: function (elem) {
        const approveButton = $(elem).prop('disabled', true);
        const post = approveButton.closest('.post');
        if (!post.hasClass('inactive')) {
            return;
        }

        const id = post.data('id');
        commentApi.approve(id).done(function () {
            post.removeClass('inactive');
            approveButton.remove();
        }).fail(function () {
            approveButton.prop('disabled', false);
        });
    },

    delete: function (elem) {
        const deleteButton = $(elem).prop('disabled', true);
        const post = deleteButton.closest('.post');

        const id = post.data('id');
        commentApi.delete(id).done(function () {
            post.remove();
        }).fail(function () {
            deleteButton.prop('disabled', false);
        });
    },

    updateUsername: function (elem) {
        const username = $(elem).val().replace(/[^ёа-яa-z0-9_\s]/gi, '');

        localStorage.setItem('comment-username', username);
        $('[name=username]').val(username).removeClass('is-invalid');
    },

    toggleAnswer: function (elem) {
        const toggleButton = $(elem);
        if (toggleButton.attr('data-active') == 1) {
            commentWidget._cancelAnswer(toggleButton);
            return;
        }

        commentWidget._showAnswer(toggleButton)
    },

    _showAnswer: function (toggleButton) {
        const commentsListWrapper = toggleButton.closest('.comments-list-wrapper');
        const commentFormContainer = commentsListWrapper.children('.comment-form-container');
        const post = toggleButton.closest('.post');

        commentsListWrapper.find('.toggle-answer[data-active=1]').each(function () {
            commentWidget._cancelAnswer($(this), true);
        })

        const id = post.data('id');
        const commentAnswerPlaceholder = post.find(`.comment-answer-placeholder[data-id=${id}]`);

        toggleButton.text('отмена').attr('data-active', 1);
        const textarea = commentFormContainer
            .clone()
            .data('parent-id', id)
            .prependTo(commentAnswerPlaceholder)
            .find('textarea')
            .css('min-height', '50px')
            .css('height', '50px')
            .on('input', function () {
                commentWidget._resizeTextarea(this);
            }).val('')
            .focus();
        commentWidget._resizeTextarea(textarea[0]);
    },

    /**
     * @param toggleButton
     * @param {boolean} checkEmpty
     * @private
     */
    _cancelAnswer: function (toggleButton, checkEmpty = false) {
        const post = toggleButton.closest('.post');
        const id = post.data('id');
        const commentAnswerPlaceholder = post.find(`.comment-answer-placeholder[data-id=${id}]`);

        if (checkEmpty) {
            const textarea = commentAnswerPlaceholder.find('textarea');
            const text = textarea.val().trim();

            if (text.length > 0) {
                return;
            }
        }

        toggleButton.text('ответить').attr('data-active', null);
        commentAnswerPlaceholder.html('');
    }
}

$(function () {
    $('.comments-list-wrapper').each(function () {
        commentWidget.init($(this));
    })
});
