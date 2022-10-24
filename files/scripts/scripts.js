
function resetReport(parameters){
    $('#main-container').html('');
    $('#main-btn-container').hide();
    $('#loading-container label').html('Please wait, retrieving all orders ... <i class="fa fa-refresh fa-spin fa-2x fa-fw"></i>');
    $('#loading-container').show();
    var paramsArray = parameters.split('&');
    var newParamsArray = [];
    $.each(paramsArray, function(i, v){
        var thisArray = v.split('=');
        if(thisArray[0] !== 'orders'){
            newParamsArray.push(v);
        }
    });
    var params = '';
    if(newParamsArray.length > 0){
        params = newParamsArray.join('&');
    }
    else {
        params = 'group=' + group;
    }
    $( "button" ).off( "click");
    getReport(params);
}

function sortGrid(event, field, sortDir) {
    const columnState = { // https://www.ag-grid.com/javascript-grid-column-state/#column-state-interface
      state: [
        {
          colId: field,
          sort: sortDir
        }
      ]
    }
    event.columnApi.applyColumnState(columnState);
  }

function getReport(p){
    $.getJSON( "files/scripts/getTable.php", { params: p} ).done(function( json ) {
        var count = json.count;
        var type = json.type;
        var group = json.group;
        if(count == 0){
            if(type == 'limitedOrders'){
                var msg = 'No orders have been retrieved. <a href="#" id="msgReset">View all orders</a>, or obtain an entitlement report by contacting Ovid Customer Support via support@ovid.com.';
                $('#msgContainer').html(msg);
                $('#msgReset').click(function(){
                    resetReport(p);
                });
            }
            else {
                var msg = 'No orders have been retrieved. Please obtain an entitlement report by contacting Ovid Customer Support via support@ovid.com.';
            }
        }
        else {
            if(type == 'limitedOrders'){
                var resetButton = '<button type="button" class="wk-button wk-button-icon-right" id="resetReport" style="margin-right: 1rem; display: inline-block;">Show All Orders <span title="refresh" class="wk-icon-refresh"></span> <span title="spinner" class="wk-icon-spinner wk-spin" id="reset-spinner"></span></button>';
                $(resetButton).prependTo($('#main-btn-container'));
                $('#resetReport').click(function(){
                    resetReport(p);
                });
            }
            else {
                $('#resetReport').remove();
            }
            var tabArray = ['journal', 'book', 'database', 'vb', 'jbi', 'support'];
            if(count > 0){
                    var tabString = '<wk-tabs tabs-style="classic" active-tab-index="0" id="tabContent"></wk-tabs>';
                    $(tabString).prependTo($('#main-container'));
                }
                var c = 0;
                var gridOptionsArray = [];
                $.each( json, function( i, item ) {
                    //if((i !== 'count') && (i !== 'type') && (i !== 'group') && (i !== 'endCoverageIndex')){
                    if(tabArray.includes(i)){  
                        if(count > 0){
                            if(i == 'vb'){
                                var thisLabel = 'Visible Body';
                            }
                            else if(i == 'jbi'){
                                var thisLabel = 'JBI Tools';
                            }
                            else if(i == 'support'){
                                var thisLabel = 'Support & Training Resources';
                            }
                            else {
                                var thisLabel = i.charAt(0).toUpperCase() + i.slice(1) + 's';
                            }

                            if(i == 'journal'){
                                var thisContainer = '<wk-tab label="' + thisLabel + '"><div class="wk-row"><div class="wk-col-3"><div class="wk-field wk-field-small wk-field-secondary"><div class="wk-field-body"><input type="text" id="filter-text-box-' + i + '"  name="filter-text-box-' + i + '" placeholder="Filter report..." class="wk-field-input" value=""></div></div></div><div class="wk-col-3"><label class="wk-field-choice-label"><input type="checkbox" name="jnl-curr-check" value="current" id="jnl-curr-check" class="wk-field-choice"><span class="wk-field-choice-text">Show current journals only</span></label></div></div><div class="wk-advanced-table-container" id="wk-docs-advanced-table-3"><div id="' + i + '-grid" class="ag-theme-alpine" style="width: 100%; height: 100%;"></div></div></wk-tab>';
                            }
                            else if(i == 'support'){
                                var thisContainer = '<wk-tab label="' + thisLabel + '"><div id="support-container"></div></wk-tab>';
                            }
                            else {
                                var thisContainer = '<wk-tab label="' + thisLabel + '"><div class="wk-row"><div class="wk-col-3"><div class="wk-field wk-field-small wk-field-secondary"><div class="wk-field-body"><input type="text" id="filter-text-box-' + i + '"  name="filter-text-box-' + i + '" placeholder="Filter report..." class="wk-field-input" value=""></div></div></div></div><div class="wk-advanced-table-container" id="wk-docs-advanced-table-3"><div id="' + i + '-grid" class="ag-theme-alpine" style="width: 100%; height: 100%;"></div></div></wk-tab>';
                            }
                            $(thisContainer).appendTo($('#tabContent'));

                            //
                            // ag-Grid configuration
                            //

                            var defaultRowHeight = 32;
                            var sortActive = false;
                            var filterActive = false;
                            var immutableStore;

                            function renderDefaultCell(params) {
                                if(typeof params !== 'undefined'){
                                    return params.value;
                                }
                            }

                            if(i == 'journal'){
                                journalGridOptions = {
                                    // PROPERTIES
                                    columnDefs: json[i]['cols'],
                                    rowData: json[i]['rows'],
    
                                    defaultColDef: {
                                        sortable: true,
                                        filter: true,
                                        icons: {
                                            sortAscending: '<span class="wk-icon-arrow-down" aria-hidden="true"/></span>',
                                            sortDescending: '<span class="wk-icon-arrow-up" aria-hidden="true"/></span>'
                                        },
                                        editable: true,
                                        resizable: true,
                                        cellRenderer: renderDefaultCell
                                    },
    
                                    rowSelection: 'multiple',
                                    pagination: true,
                                    paginationPageSize: 25,
                                    overlayLoadingTemplate: '<span class="wk-icon-filled-spinner-alt wk-spin" aria-hidden="true"></span>',
                                    immutableData: true,
                                    onFirstDataRendered: () => console.log("onFirstDataRendered"),
                                    isExternalFilterPresent: isExternalFilterPresent,
                                    doesExternalFilterPass: doesExternalFilterPass,
                                    animateRows: true,
                                    domLayout: 'autoHeight',
                                    paginateChildRows: true,
                                    suppressRowClickSelection: true,
    
                                    getRowHeight: function () {
                                        return defaultRowHeight;
                                    },
                                    /*
                                    getRowNodeId: function (data) {
                                        if (typeof data !== 'undefined') {
                                            return data.id;
                                        }
                                    },
                                    */
                                    // EVENTS e.g.
                                    onSortChanged: function (event) {
                                        // customise logic based on your needs
                                        var sortModel = journalGridOptions.api.getSortModel();
                                        sortActive = sortModel && sortModel.length > 0;
                                        var suppressRowDrag = sortActive || filterActive;
                                        journalGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onFilterChanged: function (event) {
                                        // customise logic based on your needs
                                        filterActive = journalGridOptions.api.isAnyFilterPresent();
                                        var suppressRowDrag = sortActive || filterActive;
                                        journalGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onRowDragMove: function (event) {
                                        // customise logic based on your needs
                                        var movingNode = event.node;
                                        var overNode = event.overNode;
                                        var rowNeedsToMove = movingNode !== overNode;
                                        var moveInArray = function (arr, fromIndex, toIndex) {
                                            var element = arr[fromIndex];
                                            arr.splice(fromIndex, 1);
                                            arr.splice(toIndex, 0, element);
                                        };
    
                                        if (rowNeedsToMove) {
                                            var movingData = movingNode.data;
                                            var overData = overNode.data;
                                            var fromIndex = immutableStore.indexOf(movingData);
                                            var toIndex = immutableStore.indexOf(overData);
                                            var newStore = immutableStore.slice();
                                            moveInArray(newStore, fromIndex, toIndex);
                                            immutableStore = newStore;
                                            journalGridOptions.api.setRowData(newStore);
                                            journalGridOptions.api.clearFocusedCell();
                                        }
                                    },
                                    onGridReady: function (event) {
                                        //console.log('The grid is now ready');
                                        // apply logic based on your needs
                                        sortGrid(event, 'title', 'asc');
                                        $('#filter-text-box-' + i).keyup(function(){
                                            journalGridOptions.api.setQuickFilter($(this).val());
                                        });
                                        $('#jnl-curr-check').off('click').on('click', (function(){
                                            if(this.checked){
                                                externalFilterChanged('Current');
                                            }
                                            else {
                                                externalFilterChanged('all');
                                            }
                                        }));
                                        journalGridOptions.api.sizeColumnsToFit();
                                    },
                                    onGridSizeChanged: function(event){
                                        journalGridOptions.api.sizeColumnsToFit();
                                    },
                                    onSelectionChanged: function (event) {
                                        //console.log('Selection has changed');
                                        // apply logic based on your needs
                                    }
                                };
                                var endDate = 'all';
                                function isExternalFilterPresent() {
                                    return endDate != 'all';
                                }
                            
                                function doesExternalFilterPass(node) {
                                    switch (endDate) {
                                        case 'Current': return node.data.endCoverage == 'Current';
                                        default: return true;
                                    }
                                }
                                function externalFilterChanged(newValue) {
                                    endDate = newValue;
                                    journalGridOptions.api.onFilterChanged();
                                }
                                gridOptionsArray.push(journalGridOptions);
                            }
                            else if (i == 'book') {
                                bookGridOptions = {
                                    // PROPERTIES
                                    columnDefs: json[i]['cols'],
                                    rowData: json[i]['rows'],
    
                                    defaultColDef: {
                                        sortable: true,
                                        filter: true,
                                        icons: {
                                            sortAscending: '<span class="wk-icon-arrow-down" aria-hidden="true"/></span>',
                                            sortDescending: '<span class="wk-icon-arrow-up" aria-hidden="true"/></span>'
                                        },
                                        editable: true,
                                        resizable: true,
                                        cellRenderer: renderDefaultCell
                                    },
    
                                    rowSelection: 'multiple',
                                    pagination: true,
                                    paginationPageSize: 25,
                                    overlayLoadingTemplate: '<span class="wk-icon-filled-spinner-alt wk-spin" aria-hidden="true"></span>',
                                    immutableData: true,
                                    enableColResize: true,
                                    onFirstDataRendered: onFirstDataRendered,
                                    animateRows: true,
                                    domLayout: 'normal',
                                    paginateChildRows: true,
                                    suppressRowClickSelection: true,
    
                                    getRowHeight: function () {
                                        return defaultRowHeight;
                                    },
                                    /*
                                    getRowNodeId: function (data) {
                                        return data.id;
                                    },
                                    */
                                    // EVENTS e.g.
                                    onSortChanged: function (event) {
                                        // customise logic based on your needs
                                        var sortModel = bookGridOptions.api.getSortModel();
                                        sortActive = sortModel && sortModel.length > 0;
                                        var suppressRowDrag = sortActive || filterActive;
                                        bookGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onFilterChanged: function (event) {
                                        // customise logic based on your needs
                                        filterActive = bookOptions.api.isAnyFilterPresent();
                                        var suppressRowDrag = sortActive || filterActive;
                                        bookGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onRowDragMove: function (event) {
                                        // customise logic based on your needs
                                        var movingNode = event.node;
                                        var overNode = event.overNode;
                                        var rowNeedsToMove = movingNode !== overNode;
                                        var moveInArray = function (arr, fromIndex, toIndex) {
                                            var element = arr[fromIndex];
                                            arr.splice(fromIndex, 1);
                                            arr.splice(toIndex, 0, element);
                                        };
    
                                        if (rowNeedsToMove) {
                                            var movingData = movingNode.data;
                                            var overData = overNode.data;
                                            var fromIndex = immutableStore.indexOf(movingData);
                                            var toIndex = immutableStore.indexOf(overData);
                                            var newStore = immutableStore.slice();
                                            moveInArray(newStore, fromIndex, toIndex);
                                            immutableStore = newStore;
                                            bookGridOptions.api.setRowData(newStore);
                                            bookGridOptions.api.clearFocusedCell();
                                        }
                                    },
                                    onGridReady: function (event) {
                                        // apply logic based on your needs
                                        sortGrid(event, 'title', 'asc');
                                        $('#filter-text-box-' + i).keyup(function(){
                                            bookGridOptions.api.setQuickFilter($(this).val());
                                        });
                                        bookGridOptions.api.sizeColumnsToFit();
                                    },
                                    onGridSizeChanged: function(event){
                                        bookGridOptions.api.sizeColumnsToFit();
                                    },
                                    onSelectionChanged: function (event) {
                                        //console.log('Selection has changed');
                                        // apply logic based on your needs
                                    }
                                };
                            }
                            else if (i == 'database') {
                                databaseGridOptions = {
                                    // PROPERTIES
                                    columnDefs: json[i]['cols'],
                                    rowData: json[i]['rows'],
    
                                    defaultColDef: {
                                        sortable: true,
                                        filter: true,
                                        icons: {
                                            sortAscending: '<span class="wk-icon-arrow-down" aria-hidden="true"/></span>',
                                            sortDescending: '<span class="wk-icon-arrow-up" aria-hidden="true"/></span>'
                                        },
                                        editable: true,
                                        resizable: true,
                                        cellRenderer: renderDefaultCell
                                    },
    
                                    rowSelection: 'multiple',
                                    pagination: true,
                                    paginationPageSize: 25,
                                    overlayLoadingTemplate: '<span class="wk-icon-filled-spinner-alt wk-spin" aria-hidden="true"></span>',
                                    immutableData: true,
                                    //enableColResize: true,
                                    onFirstDataRendered: () => console.log("onFirstDataRendered"),
                                    animateRows: true,
                                    domLayout: 'normal',
                                    paginateChildRows: true,
                                    suppressRowClickSelection: true,
    
                                    getRowHeight: function () {
                                        return defaultRowHeight;
                                    },
                                    /*
                                    getRowNodeId: function (data) {
                                        return data.id;
                                    },
                                    */
                                    // EVENTS e.g.
                                    onSortChanged: function (event) {
                                        // customise logic based on your needs
                                        var sortModel = databaseGridOptions.api.getSortModel();
                                        sortActive = sortModel && sortModel.length > 0;
                                        var suppressRowDrag = sortActive || filterActive;
                                        databaseGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onFilterChanged: function (event) {
                                        // customise logic based on your needs
                                        filterActive = databaseGridOptions.api.isAnyFilterPresent();
                                        var suppressRowDrag = sortActive || filterActive;
                                        databaseGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onRowDragMove: function (event) {
                                        // customise logic based on your needs
                                        var movingNode = event.node;
                                        var overNode = event.overNode;
                                        var rowNeedsToMove = movingNode !== overNode;
                                        var moveInArray = function (arr, fromIndex, toIndex) {
                                            var element = arr[fromIndex];
                                            arr.splice(fromIndex, 1);
                                            arr.splice(toIndex, 0, element);
                                        };
    
                                        if (rowNeedsToMove) {
                                            var movingData = movingNode.data;
                                            var overData = overNode.data;
                                            var fromIndex = immutableStore.indexOf(movingData);
                                            var toIndex = immutableStore.indexOf(overData);
                                            var newStore = immutableStore.slice();
                                            moveInArray(newStore, fromIndex, toIndex);
                                            immutableStore = newStore;
                                            databaseGridOptions.api.setRowData(newStore);
                                            databaseGridOptions.api.clearFocusedCell();
                                        }
                                    },
                                    onGridReady: function (event) {
                                        // apply logic based on your needs
                                        sortGrid(event, 'title', 'asc');
                                        $('#filter-text-box-' + i).keyup(function(){
                                            databaseGridOptions.api.setQuickFilter($(this).val());
                                        });
                                        databaseGridOptions.api.sizeColumnsToFit();
                                    },
                                    onGridSizeChanged: function(event){
                                        databaseGridOptions.api.sizeColumnsToFit();
                                    },
                                    onGridSizeChanged: function(event){
                                        databaseGridOptions.api.sizeColumnsToFit();
                                    },
                                    onSelectionChanged: function (event) {
                                        //console.log('Selection has changed');
                                        // apply logic based on your needs
                                    }
                                };
                            }
                            else if (i == 'vb') {
                                vbGridOptions = {
                                    // PROPERTIES
                                    columnDefs: json[i]['cols'],
                                    rowData: json[i]['rows'],
    
                                    defaultColDef: {
                                        sortable: true,
                                        filter: true,
                                        icons: {
                                            sortAscending: '<span class="wk-icon-arrow-down" aria-hidden="true"/></span>',
                                            sortDescending: '<span class="wk-icon-arrow-up" aria-hidden="true"/></span>'
                                        },
                                        editable: true,
                                        resizable: true,
                                        cellRenderer: renderDefaultCell
                                    },
    
                                    rowSelection: 'multiple',
                                    pagination: true,
                                    paginationPageSize: 25,
                                    overlayLoadingTemplate: '<span class="wk-icon-filled-spinner-alt wk-spin" aria-hidden="true"></span>',
                                    immutableData: true,
                                    enableColResize: true,
                                    onFirstDataRendered: onFirstDataRendered,
                                    animateRows: true,
                                    domLayout: 'normal',
                                    paginateChildRows: true,
                                    suppressRowClickSelection: true,
    
                                    getRowHeight: function () {
                                        return defaultRowHeight;
                                    },
                                    /*
                                    getRowNodeId: function (data) {
                                        return data.id;
                                    },
                                    */
                                    // EVENTS e.g.
                                    onSortChanged: function (event) {
                                        // customise logic based on your needs
                                        var sortModel = vbGridOptions.api.getSortModel();
                                        sortActive = sortModel && sortModel.length > 0;
                                        var suppressRowDrag = sortActive || filterActive;
                                        vbGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onFilterChanged: function (event) {
                                        // customise logic based on your needs
                                        filterActive = vbGridOptions.api.isAnyFilterPresent();
                                        var suppressRowDrag = sortActive || filterActive;
                                        vbGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onRowDragMove: function (event) {
                                        // customise logic based on your needs
                                        var movingNode = event.node;
                                        var overNode = event.overNode;
                                        var rowNeedsToMove = movingNode !== overNode;
                                        var moveInArray = function (arr, fromIndex, toIndex) {
                                            var element = arr[fromIndex];
                                            arr.splice(fromIndex, 1);
                                            arr.splice(toIndex, 0, element);
                                        };
    
                                        if (rowNeedsToMove) {
                                            var movingData = movingNode.data;
                                            var overData = overNode.data;
                                            var fromIndex = immutableStore.indexOf(movingData);
                                            var toIndex = immutableStore.indexOf(overData);
                                            var newStore = immutableStore.slice();
                                            moveInArray(newStore, fromIndex, toIndex);
                                            immutableStore = newStore;
                                            vbGridOptions.api.setRowData(newStore);
                                            vbGridOptions.api.clearFocusedCell();
                                        }
                                    },
                                    onGridReady: function (event) {
                                        // apply logic based on your needs
                                        //sortGrid(event, 'title', 'asc');
                                        $('#filter-text-box-' + i).keyup(function(){
                                            vbGridOptions.api.setQuickFilter($(this).val());
                                        });
                                        vbGridOptions.api.sizeColumnsToFit();
                                    },
                                    onGridSizeChanged: function(event){
                                        vbGridOptions.api.sizeColumnsToFit();
                                    },
                                    onSelectionChanged: function (event) {
                                        //console.log('Selection has changed');
                                        // apply logic based on your needs
                                    }
                                };
                            }
                            else if (i == 'jbi') {
                                jbiGridOptions = {
                                    // PROPERTIES
                                    columnDefs: json[i]['cols'],
                                    rowData: json[i]['rows'],
    
                                    defaultColDef: {
                                        sortable: true,
                                        filter: true,
                                        icons: {
                                            sortAscending: '<span class="wk-icon-arrow-down" aria-hidden="true"/></span>',
                                            sortDescending: '<span class="wk-icon-arrow-up" aria-hidden="true"/></span>'
                                        },
                                        editable: true,
                                        resizable: true,
                                        cellRenderer: renderDefaultCell
                                    },
    
                                    rowSelection: 'multiple',
                                    pagination: true,
                                    paginationPageSize: 25,
                                    overlayLoadingTemplate: '<span class="wk-icon-filled-spinner-alt wk-spin" aria-hidden="true"></span>',
                                    immutableData: true,
                                    enableColResize: true,
                                    onFirstDataRendered: onFirstDataRendered,
                                    animateRows: true,
                                    domLayout: 'normal',
                                    paginateChildRows: true,
                                    suppressRowClickSelection: true,
    
                                    getRowHeight: function () {
                                        return defaultRowHeight;
                                    },
                                    /*
                                    getRowNodeId: function (data) {
                                        return data.id;
                                    },
                                    */
                                    // EVENTS e.g.
                                    onSortChanged: function (event) {
                                        // customise logic based on your needs
                                        var sortModel = jbiGridOptions.api.getSortModel();
                                        sortActive = sortModel && sortModel.length > 0;
                                        var suppressRowDrag = sortActive || filterActive;
                                        jbiGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onFilterChanged: function (event) {
                                        // customise logic based on your needs
                                        filterActive = jbiGridOptions.api.isAnyFilterPresent();
                                        var suppressRowDrag = sortActive || filterActive;
                                        jbiGridOptions.api.setSuppressRowDrag(suppressRowDrag);
                                    },
                                    onRowDragMove: function (event) {
                                        // customise logic based on your needs
                                        var movingNode = event.node;
                                        var overNode = event.overNode;
                                        var rowNeedsToMove = movingNode !== overNode;
                                        var moveInArray = function (arr, fromIndex, toIndex) {
                                            var element = arr[fromIndex];
                                            arr.splice(fromIndex, 1);
                                            arr.splice(toIndex, 0, element);
                                        };
    
                                        if (rowNeedsToMove) {
                                            var movingData = movingNode.data;
                                            var overData = overNode.data;
                                            var fromIndex = immutableStore.indexOf(movingData);
                                            var toIndex = immutableStore.indexOf(overData);
                                            var newStore = immutableStore.slice();
                                            moveInArray(newStore, fromIndex, toIndex);
                                            immutableStore = newStore;
                                            jbiGridOptions.api.setRowData(newStore);
                                            jbiGridOptions.api.clearFocusedCell();
                                        }
                                    },
                                    onGridReady: function (event) {
                                        // apply logic based on your needs
                                        sortGrid(event, 'title', 'asc');
                                        $('#filter-text-box-' + i).keyup(function(){
                                            jbiGridOptions.api.setQuickFilter($(this).val());
                                        });
                                        jbiGridOptions.api.sizeColumnsToFit();
                                    },
                                    onGridSizeChanged: function(event){
                                        jbiGridOptions.api.sizeColumnsToFit();
                                    },
                                    onSelectionChanged: function (event) {
                                        //console.log('Selection has changed');
                                        // apply logic based on your needs
                                    }
                                };
                            }
                            else if(i == 'support'){
                                var supt = json[i];
                                var docFrag = document.createDocumentFragment();
                                $.each(supt, function(k,v){
                                    var suptLabel = supt[k]['label'];
                                    var suptData = supt[k]['data'];
                                    var thisDataString = '';
                                    if(k == 'icons'){
                                        iconArray = suptData;
                                        var dlBtn = '<button type="button" class="wk-button wk-button-icon-right wk-button-small" style="margin:5px" id="icon-dl">Download all icons <span title="download-line" class="wk-icon-download-line"></span> <span title="spinner" class="wk-icon-spinner wk-spin" id="icondl-spinner"></span></button>';
                                    }
                                    else if(k == 'qrc'){
                                        qrcArray = suptData;
                                        var dlBtn = '<button type="button" class="wk-button wk-button-icon-right wk-button-small" style="margin:5px" id="qrc-dl">Download all QRCs <span title="download-line" class="wk-icon-download-line"></span> <span title="spinner" class="wk-icon-spinner wk-spin" id="qrcdl-spinner"></span></button>';
                                    }
                                    else if(k == 'videos'){
                                        videoArray = suptData;
                                        var dlBtn = '<button type="button" class="wk-button wk-button-icon-right wk-button-small" style="margin:5px" id="video-dl">Download all Video Links <span title="download-line" class="wk-icon-download-line"></span> <span title="spinner" class="wk-icon-spinner wk-spin" id="videodl-spinner"></span></button>';
                                    }
                                    $.each(suptData, function(x,y){
                                        if(k == 'icons'){
                                            var thisURL = suptData[x]['url'];
                                            var thisHTML = '<div class="wk-row"><div class="wk-col-12 img-row"><p><a href="https://tools.ovid.com/ovidtools/images/toolkit/' + thisURL + '" target=_blank><img class="supt-icon" src="https://tools.ovid.com/ovidtools/images/toolkit/' + thisURL + '" style="max-width:100%"/></a></p></div></div>';
                                            thisDataString = thisDataString + thisHTML;
                                        }
                                        else if(k == 'qrc'){
                                            var thisURL = suptData[x]['url'];
                                            var thisLabel = suptData[x]['label'];
                                            var thisIMG = suptData[x]['img'];
                                            var thisHTML = '<div class="wk-col-12" align="center"><a href="' + thisURL + '" target=_blank><img class="img-fluid" src="' + thisIMG + '" style="max-width:100%"/><p style="font-size: 12px;margin-top:5px;">' + thisLabel + '</a></div>';
                                            thisDataString = thisDataString + thisHTML;
                                        }
                                        else if(k == 'videos'){
                                            var thisURL = suptData[x]['url'];
                                            var thisLabel = suptData[x]['label'];
                                            var thisHTML = '<li><a href="' + thisURL + '" target=_blank>' + thisLabel + '</a></li>';
                                            thisDataString = thisDataString + thisHTML;
                                          }
                                    });
                                    if(k == 'videos'){
                                        thisDataString = '<div class="wk-col-12" align="center"><ul style="text-align:left">' + thisDataString + '</ul></div>';
                                    }
                                    var tempNode = $('.card-template').clone();
                                    $(tempNode).find('.wk-card-container-header h3').text(suptLabel);
                                    $(tempNode).find('.wk-card-container-body').html(thisDataString);
                                    $(tempNode).find('.card-buttons').html(dlBtn);
                                    $(tempNode).find('.wk-card-container-header').css('padding-top', '0');
                                    $(tempNode).css('display', 'inline-block');
                                    $(tempNode).appendTo(docFrag);
                                });
                                //create QR Code card
                                var tempNode = $('.card-template').clone();
                                $(tempNode).find('.wk-card-container-header h3').text('QR Codes');
                                var thisDataString = '<div class="wk-col-12" align="left"><p>Generate QR Codes for your Ovid resources.</p><p style="margin-top:5px"><em>QR codes generated for current journals only.</em></p></div>';
                                $(tempNode).find('.wk-card-container-body').html(thisDataString);
                                var dlBtn = '<button type="button" class="wk-button wk-button-icon-right wk-button-small" style="margin:5px" id="qrcode-dl">Download QR Codes <span title="download-line" class="wk-icon-download-line"></span> <span title="spinner" class="wk-icon-spinner wk-spin" id="qrcode-spinner"></span></button>';
                                $(tempNode).find('.card-buttons').html(dlBtn);
                                $(tempNode).find('.wk-card-container-header').css('padding-top', '0');
                                $(tempNode).css('display', 'inline-block');
                                $(tempNode).appendTo(docFrag);

                                $(docFrag).appendTo('#support-container');
                                if(typeof iconArray !== 'undefined'){
                                    $('#icon-dl').click(function(){
                                        $('#icondl-spinner').css('display', 'inline-block');
                                        xhttp = new XMLHttpRequest();
                                        xhttp.onreadystatechange = function() {
                                            var a;
                                            if (xhttp.readyState === 4 && xhttp.status === 200) {
                                                // Trick for making downloadable link
                                                a = document.createElement('a');
                                                a.href = window.URL.createObjectURL(xhttp.response);
                                                // Give filename you wish to download
                                                let today = new Date().toISOString().slice(0, 10)
                                                var filename = "Ovid Icons " + group + " " + today + ".zip";
                                                a.download = filename;
                                                a.style.display = 'none';
                                                document.body.appendChild(a);
                                                a.click();
                                                $('#icondl-spinner').css('display', 'none');
                                             }
                                        };
                                        // Post data to URL which handles post request
                                        xhttp.open("POST", "files/scripts/icondl.php");
                                        xhttp.setRequestHeader("Content-Type", "application/json");
                                        // You should set responseType as blob for binary responses
                                        xhttp.responseType = 'blob';
                                        xhttp.send(JSON.stringify(iconArray));
                                    });
                                }
                                if(typeof qrcArray !== 'undefined'){
                                    $('#qrc-dl').click(function(){
                                        $('#qrcdl-spinner').css('display', 'inline-block');
                                        xhttp = new XMLHttpRequest();
                                        xhttp.onreadystatechange = function() {
                                            var a;
                                            if (xhttp.readyState === 4 && xhttp.status === 200) {
                                                // Trick for making downloadable link
                                                a = document.createElement('a');
                                                a.href = window.URL.createObjectURL(xhttp.response);
                                                // Give filename you wish to download
                                                let today = new Date().toISOString().slice(0, 10)
                                                var filename = "Ovid QRCs " + group + " " + today + ".zip";
                                                a.download = filename;
                                                a.style.display = 'none';
                                                document.body.appendChild(a);
                                                a.click();
                                                $('#qrcdl-spinner').css('display', 'none');
                                             }
                                        };
                                        // Post data to URL which handles post request
                                        xhttp.open("POST", "files/scripts/qrcdl.php");
                                        xhttp.setRequestHeader("Content-Type", "application/json");
                                        // You should set responseType as blob for binary responses
                                        xhttp.responseType = 'blob';
                                        xhttp.send(JSON.stringify(qrcArray));
                                    });
                                }
                                if(typeof videoArray !== 'undefined'){
                                    $('#video-dl').click(function(){
                                        $('#videodl-spinner').css('display', 'inline-block');
                                        xhttp = new XMLHttpRequest();
                                        xhttp.onreadystatechange = function() {
                                            var a;
                                            if (xhttp.readyState === 4 && xhttp.status === 200) {
                                                // Trick for making downloadable link
                                                a = document.createElement('a');
                                                a.href = window.URL.createObjectURL(xhttp.response);
                                                // Give filename you wish to download
                                                let today = new Date().toISOString().slice(0, 10)
                                                var filename = "Ovid Videos " + group + " " + today + ".xlsx";
                                                a.download = filename;
                                                a.style.display = 'none';
                                                document.body.appendChild(a);
                                                a.click();
                                                $('#videodl-spinner').css('display', 'none');
                                             }
                                        };
                                        // Post data to URL which handles post request
                                        xhttp.open("POST", "files/scripts/videodl.php");
                                        xhttp.setRequestHeader("Content-Type", "application/json");
                                        // You should set responseType as blob for binary responses
                                        xhttp.responseType = 'blob';
                                        xhttp.send(JSON.stringify(videoArray));
                                    });
                                }
                                $('#qrcode-dl').click(function(){
                                    $('#qrcode-spinner').css('display', 'inline-block');
                                    var qrArray = [];
                                    var regex = /(<([^>]+)>)/ig;
                                    if (typeof bookGridOptions !== 'undefined') {
                                        var rowArray = [];
                                        bookGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                                            if(rowNode.data.jumpStart){
                                                var jStart = rowNode.data.jumpStart;
                                                jStart = jStart.replace(regex, "");
                                                rowNode.data.jumpStart = jStart;
                                            }
                                            if(rowNode.data.shibJumpstart){
                                                var shib = rowNode.data.shibJumpstart;
                                                shib = shib.replace(regex, "");
                                                rowNode.data.shibJumpstart = shib;
                                            }
                                            rowArray.push(rowNode.data);
                                        });
                                        var thisArray = [];
                                        thisArray.push('Books');
                                        thisArray.push(rowArray);
                                        qrArray.push(thisArray);
                                    }

                                    if (typeof journalGridOptions !== 'undefined') {
                                        var rowArray = [];
                                        journalGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                                            if(rowNode.data.endCoverage){
                                                if(rowNode.data.endCoverage == 'Current'){
                                                    if(rowNode.data.jumpStart){
                                                        var jStart = rowNode.data.jumpStart;
                                                        jStart = jStart.replace(regex, "");
                                                        rowNode.data.jumpStart = jStart;
                                                    }
                                                    if(rowNode.data.shibJumpstart){
                                                        var shib = rowNode.data.shibJumpstart;
                                                        shib = shib.replace(regex, "");
                                                        rowNode.data.shibJumpstart = shib;
                                                    }
                                                    rowArray.push(rowNode.data);
                                                }
                                            }
                                        });
                                        var thisArray = [];
                                        thisArray.push('Journals');
                                        thisArray.push(rowArray);
                                        qrArray.push(thisArray);
                                    }

                                    if (typeof databaseGridOptions !== 'undefined') {
                                        var rowArray = [];
                                        databaseGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                                            if(rowNode.data.jumpStart){
                                                var jStart = rowNode.data.jumpStart;
                                                jStart = jStart.replace(regex, "");
                                                rowNode.data.jumpStart = jStart;
                                            }
                                            if(rowNode.data.shibJumpstart){
                                                var shib = rowNode.data.shibJumpstart;
                                                shib = shib.replace(regex, "");
                                                rowNode.data.shibJumpstart = shib;
                                            }
                                            rowArray.push(rowNode.data);
                                        });
                                        var thisArray = [];
                                        thisArray.push('Databases');
                                        thisArray.push(rowArray);
                                        qrArray.push(thisArray);
                                    }

                                    if (typeof vbGridOptions !== 'undefined') {
                                        var rowArray = [];
                                        vbGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                                            if(rowNode.data.jumpStart){
                                                var jStart = rowNode.data.jumpStart;
                                                jStart = jStart.replace(regex, "");
                                                rowNode.data.jumpStart = jStart;
                                            }
                                            if(rowNode.data.shibJumpstart){
                                                var shib = rowNode.data.shibJumpstart;
                                                shib = shib.replace(regex, "");
                                                rowNode.data.shibJumpstart = shib;
                                            }
                                            rowArray.push(rowNode.data);
                                        });
                                        var thisArray = [];
                                        thisArray.push('Visible Body');
                                        thisArray.push(rowArray);
                                        qrArray.push(thisArray);
                                    }

                                    if (typeof jbiGridOptions !== 'undefined') {
                                        var rowArray = [];
                                        jbiGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                                            if(rowNode.data.jumpStart){
                                                var jStart = rowNode.data.jumpStart;
                                                jStart = jStart.replace(regex, "");
                                                rowNode.data.jumpStart = jStart;
                                            }
                                            if(rowNode.data.shibJumpstart){
                                                var shib = rowNode.data.shibJumpstart;
                                                shib = shib.replace(regex, "");
                                                rowNode.data.shibJumpstart = shib;
                                            }
                                            rowArray.push(rowNode.data);
                                        });
                                        var thisArray = [];
                                        thisArray.push('JBI Tools');
                                        thisArray.push(rowArray);
                                        qrArray.push(thisArray);
                                    }
                                    
                                    xhttp = new XMLHttpRequest();
                                    xhttp.onreadystatechange = function() {
                                        var a;
                                        if (xhttp.readyState === 4 && xhttp.status === 200) {
                                            // Trick for making downloadable link
                                            a = document.createElement('a');
                                            a.href = window.URL.createObjectURL(xhttp.response);
                                            // Give filename you wish to download
                                            let today = new Date().toISOString().slice(0, 10)
                                            var filename = "Ovid QR Codes " + group + " " + today + ".zip";
                                            a.download = filename;
                                            a.style.display = 'none';
                                            document.body.appendChild(a);
                                            a.click();
                                            $('#qrcode-spinner').css('display', 'none');
                                         }
                                    };
                                    // Post data to URL which handles post request
                                    xhttp.open("POST", "files/scripts/qrcode.php");
                                    xhttp.setRequestHeader("Content-Type", "application/json");
                                    // You should set responseType as blob for binary responses
                                    xhttp.responseType = 'blob';
                                    xhttp.send(JSON.stringify(qrArray));
                                });
                            }
                            /*
                            function onFirstDataRendered(params) {
                                if (typeof params !== 'undefined') {
                                    params.api.sizeColumnsToFit();
                                    setTimeout(function ()
                                    {
                                    $scope.gridOptions.api.refreshView();
                                    }, 0);
                                    //params.columnApi.autoSizeColumns(['jumpStart'], true)
                                }
                            }*/

                            function onFirstDataRendered(params) {
                                if (typeof params !== 'undefined') {
                                    params.api.sizeColumnsToFit();
                                }
                            }

                            if(i == 'journal'){
                                if(json.endCoverageIndex){
                                    var endCov = json.endCoverageIndex;
                                }
                                else {
                                    var endCov = 3;
                                }
                                var wkGrid = document.querySelector('#journal-grid');
                                new agGrid.Grid(wkGrid, journalGridOptions);
                            }
                            else if(i == 'book'){
                                var wkGrid = document.querySelector('#book-grid');
                                new agGrid.Grid(wkGrid, bookGridOptions);
                            }
                            else if(i == 'database'){
                                var wkGrid = document.querySelector('#database-grid');
                                new agGrid.Grid(wkGrid, databaseGridOptions);
                            }
                            else if(i == 'vb'){
                                var wkGrid = document.querySelector('#vb-grid');
                                new agGrid.Grid(wkGrid, vbGridOptions);
                            }
                            else if(i == 'jbi'){
                                var wkGrid = document.querySelector('#jbi-grid');
                                new agGrid.Grid(wkGrid, jbiGridOptions);
                            }
                        }
                        else {
                            var thisContainer = item;
                            $(thisContainer).prependTo($('#main-container'));
                        }
                        c++;
                    }
                });
                $('#excel-dl').click(function(){
                    $('#excel-spinner').css('display', 'inline-block');
                    if (typeof rowNode !== 'undefined'){
                        delete rowNode.data.jumpStart;
                    }
                    var exptSheets = [];
                    var regex = /(<([^>]+)>)/ig;
                    if (typeof journalGridOptions !== 'undefined') {
                        var cols = journalGridOptions.columnDefs;
                        var headerArray = [];
                        for (var key in cols) {
                            if (cols.hasOwnProperty(key)) {
                                headerArray.push(cols[key]['headerName']);
                            }
                        }
                        var rowArray = [];
                        rowArray.push(headerArray);
                        journalGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                            if(rowNode.data.jumpStart){
                                var jStart = rowNode.data.jumpStart;
                                jStart = jStart.replace(regex, "");
                                rowNode.data.jumpStart = jStart;
                            }
                            if(rowNode.data.ejpURL){
                                var ejp = rowNode.data.ejpURL;
                                ejp = ejp.replace(regex, "");
                                rowNode.data.ejpURL = ejp;
                            }
                            if(rowNode.data.shibJumpstart){
                                var shib = rowNode.data.shibJumpstart;
                                shib = shib.replace(regex, "");
                                rowNode.data.shibJumpstart = shib;
                            }
                            rowArray.push(rowNode.data);
                        });
                        var thisArray = [];
                        thisArray.push('Journals');
                        thisArray.push(rowArray);
                        exptSheets.push(thisArray);
                    }
                    if (typeof bookGridOptions !== 'undefined') {
                        var cols = bookGridOptions.columnDefs;
                        var headerArray = [];
                        for (var key in cols) {
                            if (cols.hasOwnProperty(key)) {
                                headerArray.push(cols[key]['headerName']);
                            }
                        }
                        var rowArray = [];
                        rowArray.push(headerArray);
                        bookGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                            if(rowNode.data.jumpStart){
                                var jStart = rowNode.data.jumpStart;
                                jStart = jStart.replace(regex, "");
                                rowNode.data.jumpStart = jStart;
                            }
                            if(rowNode.data.shibJumpstart){
                                var shib = rowNode.data.shibJumpstart;
                                shib = shib.replace(regex, "");
                                rowNode.data.shibJumpstart = shib;
                            }
                            rowArray.push(rowNode.data);
                        });
                        var thisArray = [];
                        thisArray.push('Books');
                        thisArray.push(rowArray);
                        exptSheets.push(thisArray);
                    }
                    if (typeof databaseGridOptions !== 'undefined') {
                        var cols = databaseGridOptions.columnDefs;
                        var headerArray = [];
                        for (var key in cols) {
                            if (cols.hasOwnProperty(key)) {
                                headerArray.push(cols[key]['headerName']);
                            }
                        }
                        var rowArray = [];
                        rowArray.push(headerArray);
                        databaseGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                            if(rowNode.data.jumpStart){
                                var jStart = rowNode.data.jumpStart;
                                jStart = jStart.replace(regex, "");
                                rowNode.data.jumpStart = jStart;
                            }
                            if(rowNode.data.shibJumpstart){
                                var shib = rowNode.data.shibJumpstart;
                                shib = shib.replace(regex, "");
                                rowNode.data.shibJumpstart = shib;
                            }
                            rowArray.push(rowNode.data);
                        });
                        var thisArray = [];
                        thisArray.push('Databases');
                        thisArray.push(rowArray);
                        exptSheets.push(thisArray);
                    }
                    if (typeof vbGridOptions !== 'undefined') {
                        var cols = vbGridOptions.columnDefs;
                        var headerArray = [];
                        for (var key in cols) {
                            if (cols.hasOwnProperty(key)) {
                                headerArray.push(cols[key]['headerName']);
                            }
                        }
                        var rowArray = [];
                        rowArray.push(headerArray);
                        vbGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                            if(rowNode.data.title){
                                var thisTitle = rowNode.data.title;
                                thisTitle = thisTitle.replace(regex, "");
                                rowNode.data.title = thisTitle;
                            }
                            if(rowNode.data.jumpStart){
                                var jStart = rowNode.data.jumpStart;
                                jStart = jStart.replace(regex, "");
                                rowNode.data.jumpStart = jStart;
                            }
                            if(rowNode.data.shibJumpstart){
                                var shib = rowNode.data.shibJumpstart;
                                shib = shib.replace(regex, "");
                                rowNode.data.shibJumpstart = shib;
                            }
                            rowArray.push(rowNode.data);
                        });
                        var thisArray = [];
                        thisArray.push('Visible Body');
                        thisArray.push(rowArray);
                        exptSheets.push(thisArray);
                    }
                    if (typeof jbiGridOptions !== 'undefined') {
                        var cols = jbiGridOptions.columnDefs;
                        var headerArray = [];
                        for (var key in cols) {
                            if (cols.hasOwnProperty(key)) {
                                headerArray.push(cols[key]['headerName']);
                            }
                        }
                        var rowArray = [];
                        rowArray.push(headerArray);
                        jbiGridOptions.api.forEachNodeAfterFilter((rowNode, index) => {
                            if(rowNode.data.jumpStart){
                                var jStart = rowNode.data.jumpStart;
                                jStart = jStart.replace(regex, "");
                                rowNode.data.jumpStart = jStart;
                            }
                            if(rowNode.data.shibJumpstart){
                                var shib = rowNode.data.shibJumpstart;
                                shib = shib.replace(regex, "");
                                rowNode.data.shibJumpstart = shib;
                            }
                            rowArray.push(rowNode.data);
                        });
                        var thisArray = [];
                        thisArray.push('JBI Tools');
                        thisArray.push(rowArray);
                        exptSheets.push(thisArray);
                    }
                    xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        var a;
                        if (xhttp.readyState === 4 && xhttp.status === 200) {
                            // Trick for making downloadable link
                            a = document.createElement('a');
                            a.href = window.URL.createObjectURL(xhttp.response);
                            // Give filename you wish to download
                            let today = new Date().toISOString().slice(0, 10)
                            var filename = "Ovid Entitlement Report " + group + " " + today + ".xlsx";
                            a.download = filename;
                            a.style.display = 'none';
                            document.body.appendChild(a);
                            a.click();
                            $('#excel-spinner').css('display', 'none');
                         }
                    };
                    // Post data to URL which handles post request
                    xhttp.open("POST", "files/scripts/dlExcel.php");
                    xhttp.setRequestHeader("Content-Type", "application/json");
                    // You should set responseType as blob for binary responses
                    xhttp.responseType = 'blob';
                    xhttp.send(JSON.stringify(exptSheets));

                    /*
                    $.ajax({
                        type: "POST",
                        url: "files/scripts/dlExcel2.php",
                        data: JSON.stringify({ json: exptSheets }),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        success: function(data){console.log(data)},
                        error: function(errMsg) {
                            alert(errMsg);
                        }
                    });
                    $.post( "files/scripts/dlExcel2.php", {json: JSON.stringify(exptSheets)}, function( expData ) {
                        console.log(expData);
                    });
                    */
                });
            }

    }).fail(function( jqxhr, textStatus, error ) {
        var err = textStatus + ", " + error;
        $('#msgContainer').html("Request Failed: " + err);
    }).always(function() {
        $('#loading-container').hide();
        $('#main-btn-container').show();
        $('#excel-dl').show();
        $('.wk-footer').removeClass('fixed-footer');
    });
    $('.dt-buttons').prependTo( "#main-container" );
    $('#optionSave').click(function(){
        var proxy = $('#proxyPrefix').val().trim();
        if((proxy != '') || ($('#encodeJstarts').is(":checked"))){
            $.each(jstartColIndexArray, function(i, item){
                var thisTableID = i + 'Table';
                var table = $("#" + thisTableID).DataTable();
                table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    var data = this.data();
                    var anchorText = data[item];
                    var thisLink = $(anchorText).attr('href');
                    var newLink = '';
                    if((proxy != '') && (thisLink.indexOf(proxy) < 0)){
                        if($('#encodeJstarts').is(":checked")){
                            thisLink = encodeURIComponent(thisLink);
                        }
                        newLink = proxy + thisLink;
                    }
                    else if($('#encodeJstarts').is(":checked")){
                        newLink = encodeURIComponent(thisLink);
                    }
                    else {
                        newLink = thisLink;
                    }
                    var anchorText = '<a href="' + newLink + '">' + newLink + '</a>';
                    data[item] = anchorText;
                    this.data(data);
                } );
            });
        }
        $('#linkOptions').modal('hide');
    });
}

$( document ).ready(function() {
    jstartColIndexArray = {};
    var params = window.location.search.substring(1);
    getReport(params);
    $('#optionReset').click(function(){
        /*
        var msg = 'Please wait, resetting report... <i class="fa fa-refresh fa-spin fa-2x fa-fw"></i>';
        $('#msgContainer').html(msg);
        $('#linkOptions').modal('hide');
        $('#modalDialog').modal('show');
        */
        $( "#contentTabs, #tabContent, .reportingWrapper, #resetReport, .dt-buttons, #optionsGearContainer" ).remove();
        $('#proxyPrefix').val('');
        $('#encodeJstarts').prop('checked', false);
        jstartColIndexArray = {};
        $('#optionSave').unbind();
        var params = window.location.search.substring(1);
        getReport(params);
    });
});

/*
$(window).load(function()
{
    var msg = 'Please wait, loading report... <i class="fa fa-refresh fa-spin fa-2x fa-fw"></i>';
    $('#msgContainer').html(msg);
    $('#modalDialog').modal('show');
});
*/