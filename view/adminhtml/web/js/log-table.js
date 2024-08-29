define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/lib/view/utils/async',
    'datatables'
], function ($, Component, async) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ProDevTools_LogManager/log-table-template',
            ajaxUrl: '',
            redirectUrl: ''
        },

        initialize: function () {
            this._super();
            this.initTable();
            return this;
        },

        initTable: function () {
            const ajaxUrl = this.ajaxUrl;
            const redirectUrl = this.redirectUrl;

            async.async('#logTable', function (logTableElement) {
                if (!$.fn.DataTable.isDataTable(logTableElement)) {
                    $(logTableElement).DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": ajaxUrl,
                            "type": "GET",
                            "dataSrc": function (json) {
                                if (json.error) {
                                    window.location.href = redirectUrl;
                                    return [];
                                }
                                return json.data;
                            }
                        },
                        "columns": [
                            { "data": "datetime" },
                            { "data": "channel" },
                            { "data": "level" },
                            { "data": "message" },
                            { "data": "context" }
                        ]
                    });
                }
            });
        }
    });
});
