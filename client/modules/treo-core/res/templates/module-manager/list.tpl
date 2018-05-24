<style>
    .modules-installed table td.cell,
    .modules-available table td.cell{
        white-space: normal;
        text-overflow: ellipsis
    }
    .install-module-row {
        background-color: #b7ffd5;
    }
    .update-module-row {
        background-color: #c4e2ff;
    }
    .remove-module-row {
        background-color: #ffcece;
    }
</style>
<div class="page-header">{{{header}}}</div>
<div class="detail-button-container button-container record-buttons clearfix">
    <div class="btn-group pull-left" role="group">
        <button class="btn btn-primary action" data-action="runUpdate" type="button">{{translate 'Run Update' scope='ModuleManager' category='labels'}}</button>
    </div>
    <div class="clearfix"></div>
</div>
<div class="panel panel-default" style="margin-left: -15px; margin-right: -15px;">
    <div class="panel-heading">
        <h4 class="panel-title">
            <span style="cursor: pointer;" class="action" title="{{translate 'clickToRefresh' category='messages'}}" data-action="refresh" data-collection="installed">{{translate 'Modules (installed)' scope='ModuleManager' category='labels'}}</span>
        </h4>
    </div>
    <div class="panel-body">
        <div class="list-container modules-installed">{{{list}}}</div>
    </div>
</div>
<div class="panel panel-default" style="margin-left: -15px; margin-right: -15px;">
    <div class="panel-heading">
        <h4 class="panel-title">
            <span style="cursor: pointer;" class="action" title="{{translate 'clickToRefresh' category='messages'}}" data-action="refresh" data-collection="available">{{translate 'Modules (available)' scope='ModuleManager' category='labels'}}</span>
        </h4>
    </div>
    <div class="panel-body">
        <div class="list-container modules-available">{{{listAvailable}}}</div>
    </div>
</div>