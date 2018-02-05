Espo.define('treo-crm:views/progress-manager/modals/show-message', 'views/modal',
    Dep => Dep.extend({

        className: 'dialog progress-modal',

        template: 'treo-crm:progress-manager/modals/show-message',

        buttonList: [
            {
                name: 'cancel',
                label: 'Close'
            }
        ],

        setup() {
            Dep.prototype.setup.call(this);

            this.header = this.translate('message', 'labels', 'ProgressManager');
        },

        data() {
            return {
                message: this.options.message
            };
        },

    })
);

