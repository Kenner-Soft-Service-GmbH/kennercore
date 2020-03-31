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
 * KennerCore is TreoCore-based Open Source application.
 * Copyright (C) 2020 KenerSoft Service GmbH
 * Website: https://kennersoft.de
 *
 * TreoCore as well as EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoCore as well as EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
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

Espo.define('treo-core:views/fields/overview-locales-filter', 'treo-core:views/fields/dropdown-enum',
    Dep => Dep.extend({

        optionsList: [
            {
                name: '',
                selectable: true
            },
            {
                name: 'showGenericFields',
                action: 'showGenericFields',
                field: true,
                type: 'bool',
                view: 'treo-core:views/fields/bool-with-inline-label',
                default: true
            }
        ],

        prepareOptionsList() {
            let locales = this.getConfig().get('inputLanguageList') || [];
            if (this.getConfig().get('isMultilangActive') && locales.length) {
                locales.forEach((locale, index) => {
                    if (!this.optionsList.find(item => item.name === locale)) {
                        let item = {
                            name: locale,
                            selectable: true,
                            label: this.getLanguage().translateOption(locale, 'language', 'Global')
                        };
                        this.optionsList.splice(1 + index, 0, item);
                    }
                });
            }

            Dep.prototype.prepareOptionsList.call(this);
        }

    })
);