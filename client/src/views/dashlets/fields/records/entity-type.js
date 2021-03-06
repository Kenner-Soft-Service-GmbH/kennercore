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
Espo.define('views/dashlets/fields/records/entity-type', 'views/fields/enum', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.on('change', function () {
                var o = {
                    primaryFilter: null,
                    boolFilterList: [],
                    title: this.translate('Records', 'dashlets'),
                    sortBy: null,
                    sortDirection: 'asc'
                };
                o.expandedLayout = {
                    rows: []
                };
                var entityType = this.model.get('entityType');
                if (entityType) {
                    o.title = this.translate(entityType, 'scopeNamesPlural');
                    o.sortBy = this.getMetadata().get(['entityDefs', entityType, 'collection', 'sortBy']);
                    var asc = this.getMetadata().get(['entityDefs', entityType, 'collection', 'asc']);
                    if (asc) {
                        o.sortDirection = 'asc';
                    } else {
                        o.sortDirection = 'desc';
                    }
                    o.expandedLayout = {
                        rows: [[{name: "name", link: true, scope: entityType}]]
                    };
                }

                this.model.set(o);
            }, this);
        },

        setupOptions: function () {
            this.params.options =  Object.keys(this.getMetadata().get('scopes')).filter(function (scope) {
                if (this.getMetadata().get('scopes.' + scope + '.disabled')) return;
                if (!this.getAcl().checkScope(scope, 'read')) return;
                if (!this.getMetadata().get(['scopes', scope, 'entity'])) return;
                if (!this.getMetadata().get(['scopes', scope, 'object'])) return;

                return true;
            }, this).sort(function (v1, v2) {
                return this.translate(v1, 'scopeNames').localeCompare(this.translate(v2, 'scopeNames'));
            }.bind(this));

            this.params.options.unshift('');
        }

    });

});
