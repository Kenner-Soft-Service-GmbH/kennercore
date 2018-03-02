/*
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM ist Open Source Product Information Managegement (PIM) application,
 * based on EspoCRM.
 * Copyright (C) 2017-2018 Zinit Solutions GmbH
 * Website: http://www.treopim.com
 *
 * TreoPIM as well es EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoPIM as well as EspoCRM is distributed in the hope that it will be useful,
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
 * and "TreoPIM" word.
 */

Espo.define('views/fields/int', 'views/fields/base', function (Dep) {

    return Dep.extend({

        type: 'int',

        detailTemplate: 'fields/int/detail',

        editTemplate: 'fields/int/edit',

        searchTemplate: 'fields/int/search',

        validations: ['required', 'int', 'range'],

        thousandSeparator: ',',

        searchTypeList: ['isNotEmpty', 'isEmpty', 'equals', 'notEquals', 'greaterThan', 'lessThan', 'greaterThanOrEquals', 'lessThanOrEquals', 'between'],

        setup: function () {
            Dep.prototype.setup.call(this);
            this.setupMaxLength();

            if (this.getPreferences().has('thousandSeparator')) {
                this.thousandSeparator = this.getPreferences().get('thousandSeparator');
            } else {
                if (this.getConfig().has('thousandSeparator')) {
                    this.thousandSeparator = this.getConfig().get('thousandSeparator');
                }
            }

            if (this.params.disableFormatting) {
                this.disableFormatting = true;
            }
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            if (this.mode == 'search') {
                var $searchType = this.$el.find('select.search-type');
                this.handleSearchType($searchType.val());
            }
        },

        data: function () {
            var data = Dep.prototype.data.call(this);

            if (this.model.get(this.name) !== null && typeof this.model.get(this.name) !== 'undefined') {
                data.isNotEmpty = true;
            }
            return data;
        },

        getValueForDisplay: function () {
            var value = isNaN(this.model.get(this.name)) ? null : this.model.get(this.name);
            return this.formatNumber(value);
        },

        formatNumber: function (value) {
            if (this.disableFormatting) {
                return value;
            }
            if (value !== null) {
                var stringValue = value.toString();
                stringValue = stringValue.replace(/\B(?=(\d{3})+(?!\d))/g, this.thousandSeparator);
                return stringValue;
            }
            return '';
        },

        setupSearch: function () {
            this.events = _.extend({
                'change select.search-type': function (e) {
                    this.handleSearchType($(e.currentTarget).val());
                },
            }, this.events || {});
        },

        handleSearchType: function (type) {
            var $additionalInput = this.$el.find('input.additional');
            var $input = this.$el.find('input[name="'+this.name+'"]');

            if (type === 'between') {
                $additionalInput.removeClass('hidden');
                $input.removeClass('hidden');
            } else if (~['isEmpty', 'isNotEmpty'].indexOf(type)) {
                $additionalInput.addClass('hidden');
                $input.addClass('hidden');
            } else {
                $additionalInput.addClass('hidden');
                $input.removeClass('hidden');
            }
        },

        setupMaxLength: function () {
            var maxValue = this.model.getFieldParam(this.name, 'max');
            if (maxValue) {
                maxValue = this.formatNumber(maxValue);
                this.params.maxLength = maxValue.toString().length;
            }
        },

        validateInt: function () {
            var value = this.model.get(this.name);
            if (isNaN(value)) {
                var msg = this.translate('fieldShouldBeInt', 'messages').replace('{field}', this.translate(this.name, 'fields', this.model.name));
                this.showValidationMessage(msg);
                return true;
            }
        },

        validateRange: function () {
            var value = this.model.get(this.name);

            if (value === null) {
                return false;
            }

            var minValue = this.model.getFieldParam(this.name, 'min');
            var maxValue = this.model.getFieldParam(this.name, 'max');

            if (minValue !== null && maxValue !== null) {
                if (value < minValue || value > maxValue ) {
                    var msg = this.translate('fieldShouldBeBetween', 'messages').replace('{field}', this.translate(this.name, 'fields', this.model.name))
                                                                                .replace('{min}', minValue)
                                                                                .replace('{max}', maxValue);
                    this.showValidationMessage(msg);
                    return true;
                }
            } else {
                if (minValue !== null) {
                    if (value < minValue) {
                        var msg = this.translate('fieldShouldBeGreater', 'messages').replace('{field}', this.translate(this.name, 'fields', this.model.name))
                                                                                 .replace('{value}', minValue);
                        this.showValidationMessage(msg);
                        return true;
                    }
                } else if (maxValue !== null) {
                    if (value > maxValue) {
                        var msg = this.translate('fieldShouldBeLess', 'messages').replace('{field}', this.translate(this.name, 'fields', this.model.name))
                                                                                    .replace('{value}', maxValue);
                        this.showValidationMessage(msg);
                        return true;
                    }
                }
            }
        },

        validateRequired: function () {
            if (this.isRequired()) {
                var value = this.model.get(this.name);
                if (value === null || value === false) {
                    var msg = this.translate('fieldIsRequired', 'messages').replace('{field}', this.translate(this.name, 'fields', this.model.name));
                    this.showValidationMessage(msg);
                    return true;
                }
            }
        },

        parse: function (value) {
            value = (value !== '') ? value : null;
            if (value !== null) {
                value = value.split(this.thousandSeparator).join('');
                if (value.indexOf('.') !== -1 || value.indexOf(',') !== -1) {
                    value = NaN;
                } else {
                    value = parseInt(value);
                }
            }
            return value;
        },

        fetch: function () {
            var value = this.$el.find('[name="'+this.name+'"]').val();
            value = this.parse(value);
            var data = {};
            data[this.name] = value;
            return data;
        },

        fetchSearch: function () {
            var value = this.parse(this.$element.val());
            var type = this.$el.find('[name="'+this.name+'-type"]').val();
            var data;

            if (isNaN(value)) {
                return false;
            }

            if (type === 'between') {
                var valueTo = this.parse(this.$el.find('[name="' + this.name + '-additional"]').val());
                if (isNaN(valueTo)) {
                    return false;
                }
                data = {
                    type: type,
                    value: [value, valueTo],
                    value1: value,
                    value2: valueTo
                };
            } else if (type == 'isEmpty') {
                data = {
                    type: 'isNull',
                    typeFront: 'isEmpty'
                };
            } else if (type == 'isNotEmpty') {
                data = {
                    type: 'isNotNull',
                    typeFront: 'isNotEmpty'
                };
            } else {
                data = {
                    type: type,
                    value: value,
                    value1: value
                };
            }
            return data;
        },

        getSearchType: function () {
            return this.searchParams.typeFront || this.searchParams.type;
        }

    });
});

