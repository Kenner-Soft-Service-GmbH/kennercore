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
Espo.define('views/email/fields/subject', 'views/fields/varchar', function (Dep) {

    return Dep.extend({

        listLinkTemplate: 'email/fields/subject/list-link',

        data: function () {
            var data = Dep.prototype.data.call(this);

            data.isRead = (this.model.get('sentById') === this.getUser().id) || this.model.get('isRead');
            data.isImportant = this.model.get('isImportant');
            data.hasAttachment = this.model.get('hasAttachment');
            data.isReplied = this.model.get('isReplied');

            if (!data.isNotEmpty) {
                if (
                    this.model.get('name') !== null
                    &&
                    this.model.get('name') !== ''
                    &&
                    this.model.has('name')
                ) {
                    data.isNotEmpty = true;
                }
            }
            return data;
        },

        getValueForDisplay: function () {
            return this.model.get('name');
        },

        getAttributeList: function () {
            return ['name', 'isRead', 'isImportant'];
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, 'change', function () {
                if (this.mode == 'list' || this.mode == 'listLink') {
                    if (this.model.hasChanged('isRead') || this.model.hasChanged('isImportant')) {
                        this.reRender();
                    }
                }
            }, this);
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
        },

    });

});
