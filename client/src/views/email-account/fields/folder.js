/*
 * This file is part of EspoCRM and/or TreoCore, and/or KennerCore.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2020 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoCore is EspoCRM-based Open Source application.
 * Copyright (C) 2017-2020 TreoLabs GmbH
 * Website: https://treolabs.com
 *
 * KennerCore is TreoCore-based Open Source application.
 * Copyright (C) 2020 Kenner Soft Service GmbH
 * Website: https://kennersoft.de
 *
 * KennerCore as well as TreoCore and EspoCRM is free software:
 * you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * KennerCore as well as TreoCore and EspoCRM is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of
 * the "KennerCore", "EspoCRM" and "TreoCore" words.
 */

Espo.define('views/email-account/fields/folder', 'views/fields/base', function (Dep) {

    return Dep.extend({

        editTemplate: 'email-account/fields/folder/edit',

        events: {
            'click [data-action="selectFolder"]': function () {
                var self = this;

                this.notify('Please wait...');

                var data = {
                    host: this.model.get('host'),
                    port: this.model.get('port'),
                    ssl: this.model.get('ssl'),
                    username: this.model.get('username'),
                };

                if (this.model.has('password')) {
                    data.password = this.model.get('password');
                } else {
                    if (!this.model.isNew()) {
                        data.id = this.model.id;
                    }
                }

                $.ajax({
                    type: 'GET',
                    url: 'EmailAccount/action/getFolders',
                    data: data,
                    error: function (xhr) {
                        Espo.Ui.error(self.translate('couldNotConnectToImap', 'messages', 'EmailAccount'));
                        xhr.errorIsHandled = true;
                    },
                }).done(function (folders) {
                    this.createView('modal', 'views/email-account/modals/select-folder', {
                        folders: folders
                    }, function (view) {
                        self.notify(false);
                        view.render();

                        self.listenToOnce(view, 'select', function (folder) {
                            view.close();
                            self.addFolder(folder);
                        });
                    });
                }.bind(this));
            }
        },

        addFolder: function (folder) {
            this.$element.val(folder);
        },
    });
});
