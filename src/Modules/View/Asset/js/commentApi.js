const commentApi = {
    /**
     * @param {int} id
     * @returns {jqXHR}
     */
    approve: function (id) {
        return $.ajax({
            url: `/api/comment/${id}`,
            type: 'PATCH',
        });
    },

    /**
     * @param {string} entity
     * @param {int} entityId
     * @param {?string} filter
     * @param {?int} parentId
     * @param {int} page
     * @param {int} perPage
     * @returns {jqXHR}
     */
    load: function (
        entity,
        entityId,
        filter = null,
        parentId = null,
        page = 1,
        perPage = 20
    ) {
        const params = $.param({
            entity: entity,
            entityId: entityId,
            filter: filter,
            parent: parentId,
            page: page,
            "per-page": perPage
        });

        return $.ajax({
            url: `/api/comment?${params}`,
            type: 'GET'
        });
    },

    /**
     * @param {string} entity
     * @param {int} entityId
     * @param {?int} parentId
     * @param {string} content
     * @param {?string} name
     * @param {?string} email
     * @returns {jqXHR}
     */
    create: function (entity, entityId, parentId, content, name = null, email = null) {
        let data = {
            entity: entity,
            entityId: entityId,
            parentId: parentId,
            comment: {
                content: content,
                url: window.location.href
            }
        }

        if (name) {
            data.comment['name'] = name;
            data.comment['email'] = email;
        }

        return $.ajax({
            url: `/api/comment`,
            type: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
        });
    },

    /**
     * @param {int} id
     * @returns {jqXHR}
     */
    delete: function (id) {
        return $.ajax({
            url: `/api/comment/${id}`,
            type: 'DELETE'
        });
    },

    /**
     * @param {int} id
     * @param {string} content
     * @returns {jqXHR}
     */
    update: function (id, content) {
        const data = {
            content: content
        }

        return $.ajax({
            url: `/api/comment/${id}`,
            type: 'UPDATE',
            data: JSON.stringify(data),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
        });
    }
}
