/*
 * This file is part of EspoCRM and/or TreoCore.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2019 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoCore is EspoCRM-based Open Source application.
 * Copyright (C) 2017-2019 TreoLabs GmbH
 * Website: https://treolabs.com
 *
 * TreoCore as well as EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoCore as well as EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word
 * and "TreoCore" word.
 */

Espo.define('treo-core:views/stream/panel', 'class-replace!treo-core:views/stream/panel',
    Dep => Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            delete this.events['focus textarea.note'];

            this.events['click textarea.note'] = e => {
                this.enablePostingMode();
            };
        },

        afterRender() {
            Dep.prototype.afterRender.call(this);

            this.listenToOnce(this.collection, 'sync', () => {
                setTimeout(() => {
                    this.stopListening(this.model, 'all');
                    this.stopListening(this.model, 'destroy');
                    this.listenTo(this.model, 'all', event => {
                        if (!['sync', 'after:relate', 'after:attributesSave'].includes(event)) {
                            return;
                        }
                        let initialTotal = this.collection.total;
                        this.collection.fetchNew({
                            success: function () {
                                this.collection.total += initialTotal;
                            }.bind(this)
                        });
                    });

                    this.listenTo(this.model, 'destroy', () => {
                        this.stopListening(this.model, 'all');
                    });
                }, 500);
            });
        },
    })
);