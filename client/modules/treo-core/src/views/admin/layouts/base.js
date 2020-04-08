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

Espo.define('treo-core:views/admin/layouts/base', 'class-replace!treo-core:views/admin/layouts/base',
    Dep => Dep.extend({

        events: _.extend({}, Dep.prototype.events, {
            'click button[data-action="save"]': function () {
                this.disableButtons();
                this.notify('Saving...');
                if (!this.save(this.enableButtons.bind(this))) {
                    this.notify(false)
                }
            }
        }),

        save: function (callback) {
            const layout = this.fetch();
            const secondsBeforeReload = 2;

            if (!this.validate(layout)) {
                this.enableButtons();
                return false;
            }

            this.getHelper().layoutManager.set(this.scope, this.type, layout, function () {
                Espo.Ui.success(this.translate('successAndReload', 'messages', 'Global').replace('{value}', secondsBeforeReload));

                if (typeof callback === 'function') {
                    callback();
                }

                setTimeout(function () {
                    window.location.reload(true);
                }.bind(this), secondsBeforeReload * 1000);
            }.bind(this));
        },
    })
);
