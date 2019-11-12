<style>
    .modules-installed table td.cell,
    .modules-store table td.cell{
        white-space: normal;
        text-overflow: ellipsis
    }
    .install-module-row {
        background-color: #dfffec;
    }
    .update-module-row {
        background-color: #dbebff;
    }
    .delete-module-row {
        background-color: #ffe5e5;
    }
    .module-list-container {
        margin-left: -16px;
        margin-right: -8px;
        border-right: 1px solid #dcdcdc;
    }
    .module-list-container > .panel > .panel-heading {
        position: relative;
    }
    .module-list-container > .panel > .panel-heading .btn-group {
        top: -1px;
        right: 1px;
        position: absolute;
    }
    .module-list-container > .panel > .panel-heading .btn-group .btn[data-action="refresh"] .fas.fa-sync {
        font-size: 12px;
    }
    .log-list-container {
        border-left: 1px solid #dcdcdc;
        margin-left: -9px;
        margin-right: -15px;
    }
</style>
<div class="page-header">{{{header}}}</div>
<div class="detail-button-container button-container record-buttons clearfix" {{#if isDaemonDisabled}}style="padding: 0"{{/if}}>
    <div class="btn-group pull-left" role="group">
        {{#if isDaemonEnabled}}
        <button class="btn btn-primary action" data-action="runUpdate" type="button" {{#if disabledRunUpdateButton}}disabled{{/if}}>{{translate 'Run Update' scope='Composer' category='labels'}}</button>
        <button class="btn btn-default action" data-action="cancelUpdate" type="button" {{#if disabledRunUpdateButton}}disabled{{/if}} style="display: none;">{{translate 'Cancel'}}</button>
        {{else}}
        <div class="alert alert-danger" role="alert"><b>{{translate 'daemonDisabledLabel' scope='Composer' category='labels'}}</b><br>{{translate 'daemonDisabledDescription' scope='Composer' category='labels'}} <a href="https://github.com/treolabs/treocore#installation" class="alert-link" target="_blank">{{translate 'daemonDisabledDescriptionLink' scope='Composer' category='labels'}}</a>.</div>
        {{/if}}
    </div>
    <div class="progress-log-wrapper">
        <div class="spinner{{#unless hideLoader}} hidden{{/unless}}">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
        <span class="progress-status"></span>
    </div>
    <div class="clearfix"></div>
</div>
<div class="row">
    <div class="col-sm-8 col-xs-12">
        <div class="module-list-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="pull-right btn-group">
                        <button type="button" class="btn btn-default btn-sm action" data-action="refresh" data-collection="installed" title="{{translate 'clickToRefresh' category='messages'}}">
                            <span class="fas fa-sync"></span>
                        </button>
                    </div>
                    <h4 class="panel-title">
                        <span style="cursor: pointer;" class="action" title="{{translate 'clickToRefresh' category='messages'}}" data-action="refresh" data-collection="installed">{{translate 'Installed' scope='Composer' category='labels'}}</span>
                    </h4>
                </div>
                <div class="panel-body">
                    <div class="list-container modules-installed">{{{list}}}</div>
                </div>
            </div>
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="pull-right btn-group">
                        <button type="button" class="btn btn-info btn-sm action" data-action="refresh" data-collection="store" title="{{translate 'clickToRefresh' category='messages'}}">
                            <span class="fas fa-sync"></span>
                        </button>
                    </div>
                    <h4 class="panel-title">
                        <span style="cursor: pointer;" class="action" title="{{translate 'clickToRefresh' category='messages'}}" data-action="refresh" data-collection="store">{{translate 'Store' scope='Composer' category='labels'}}</span>
                    </h4>
                </div>
                <div class="panel-body">
                    <div class="list-container modules-store">{{{listStore}}}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-xs-12">
        <div class="log-list-container">{{{logList}}}</div>
    </div>
</div>
