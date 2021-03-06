define(
    ['jquery', 'underscore', 'oro/mediator', 'oro/datafilter/select-filter', 'pim/user-context'],
    function ($, _, mediator, SelectFilter, UserContext) {
        'use strict';

        /**
         * Scope filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  oro/datafilter/product_scope-filter
         * @class   oro.datafilter.ScopeFilter
         * @extends oro.datafilter.SelectFilter
         */
        return SelectFilter.extend({
            /**
             * @override
             * @property {Boolean}
             * @see Oro.Filter.SelectFilter
             */
            contextSearch: false,
            catalogScope: null,

            initialize: function() {
                SelectFilter.prototype.initialize.apply(this, arguments);
                this.catalogScope = UserContext.get('catalogScope');

                mediator.once('datagrid_filters:rendered', this.resetValue.bind(this));
                mediator.once('datagrid_filters:rendered', this.moveFilter.bind(this));

                mediator.bind('grid_load:complete', function(collection) {
                    $('#grid-' + collection.inputName).find('div.toolbar').show();
                });
            },

            /**
             * Move the filter to its proper position
             *
             * @param {Array} collection
             */
            moveFilter: function (collection) {
                var $grid = $('#grid-' + collection.inputName);
                this.$el.addClass('pull-right').insertBefore($grid.find('.actions-panel'));

                var $filterChoices = $grid.find('#add-filter-select');
                $filterChoices.find('option[value="scope"]').remove();
                $filterChoices.multiselect('refresh');

                this.selectWidget.multiselect('refresh');
            },

            /**
             * Update the current filter value using the UserContext.
             */
            resetValue: function () {
                this.setValue({value: this.catalogScope});
                UserContext.set('catalogScope', this.catalogScope);
                this.selectWidget.multiselect('refresh');
            },

            /**
             * @inheritDoc
             */
            disable: function () {
                return this;
            },

            /**
             * @inheritDoc
             */
            hide: function () {
                return this;
            },

            /**
             * @inheritDoc
             */
            _onValueUpdated: function (newValue) {
                UserContext.set('catalogScope', newValue.value);

                return SelectFilter.prototype._onValueUpdated.apply(this, arguments);
            },

            /**
             * Filter template
             *
             * @override
             * @property
             * @see Oro.Filter.SelectFilter
             */
            template: _.template(
                '<div class="btn filter-select filter-criteria-selector scope-filter">' +
                    '<i class="icon-eye-open" title="<%= label %>"></i>' +
                    '<select>' +
                        '<% _.each(options, function (option) { %>' +
                            '<option value="<%= option.value %>"><%= option.label %></option>' +
                        '<% }); %>' +
                    '</select>' +
                '</div>'
            )
        });
    }
);
