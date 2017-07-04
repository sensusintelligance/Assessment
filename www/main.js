/**
 * Created by Avaron on 12/24/2015.
 */
(function(domWindow)
{

    domWindow.ViewController =
    {
        s_AlertDialog: null,
        s_AlertDialogContent: null,
        s_Spinners: null,
        s_ContentViews: null,

        s_FilterDateStart: null,
        s_FilterDateEnd: null,

        s_ActiveTab: null,

        alert: function(message)
        {
            this.s_AlertDialogContent.text(message);
            this.s_AlertDialog.removeClass("inactive");

            setTimeout("ViewController.hideAlertDialog()", 1000);
        },

        hideAlertDialog: function()
        {
            this.s_AlertDialog.addClass("inactive");
        },

        toggleLoading: function(loading)
        {
            if (loading)
            {
                this.s_Spinners.removeClass("inactive");
                this.s_ContentViews.addClass("inactive");
            }
            else
            {
                this.s_Spinners.addClass("inactive");
                this.s_ContentViews.removeClass("inactive");
            }
        },

        switchTab: function(tab)
        {
            this.s_ActiveTab.removeClass("active");
            this.s_ActiveTab = $(".main."+tab);
            this.s_ActiveTab.addClass("active");
        },

        getFilterDateStart: function()
        {
            return this.s_FilterDateStart.val();
        },

        getFilterDateEnd: function()
        {
            return this.s_FilterDateEnd.val();
        },

        ContentController:
        {

            TableViewScreen:
            {
                s_ViewScreen: null,

                Table:
                {
                    s_ViewControl: null,
                    s_ViewControlContentList: null,

                    clear: function()
                    {
                        this.s_ViewControlContentList.empty();
                    },

                    addColumn: function(title, rows)
                    {
                        var assessmentTableColumn = $("<div>")
                            .addClass("column");

                        assessmentTableColumn.append($("<div>")
                            .addClass("heading")
                            .addClass("row")
                            .text(title));

                        for(var rowIndex in rows)
                            assessmentTableColumn.append($("<div>")
                                .addClass("row")
                                .text(rows[rowIndex]));

                        this.s_ViewControlContentList.append(assessmentTableColumn);
                    },

                    show: function()
                    {
                        this.s_ViewControl.removeClass("inactive");
                    },

                    initialize: function()
                    {
                        this.s_ViewControl = $("#view-assessment-table");
                        this.s_ViewControlContentList = $("#assessment-table");
                    }
                },

                populateTable: function(columnList)
                {
                    this.Table.clear();

                    for(var columnIndex in columnList)
                        this.Table.addColumn(columnList[columnIndex].title ,columnList[columnIndex].rowData);
                },

                showScreen: function()
                {
                    this.s_ViewScreen.removeClass("inactive");
                },

                hideScreen: function()
                {
                    this.s_ViewScreen.addClass("inactive");
                },

                initialize: function()
                {
                    this.s_ViewScreen = $("#view-table");
                    this.Table.initialize();
                }
            },

            BarGraphViewScreen:
            {
                s_ViewScreen: null,

                BarGraph:
                {
                    s_ViewControl: null,
                    s_ViewControlContentList: null,

                    clear: function()
                    {
                        this.s_ViewControlContentList.empty();
                    },

                    generateBarGraph: function(options)
                    {
                        var assessmentBarGraph = $("<div>").CanvasJSChart(options);

                        this.s_ViewControlContentList.append(assessmentBarGraph);
                    },

                    show: function()
                    {
                        this.s_ViewControl.removeClass("inactive");
                    },

                    initialize: function()
                    {
                        this.s_ViewControl = $("#view-assessment-bargraph");
                        this.s_ViewControlContentList = $("#assessment-bargraph");
                    }
                },

                populateBarGraph: function(barGraphOptions)
                {
                    this.BarGraph.clear();

                    this.BarGraph.generateBarGraph(barGraphOptions);
                },

                showScreen: function()
                {
                    this.s_ViewScreen.removeClass("inactive");
                },

                hideScreen: function()
                {
                    this.s_ViewScreen.addClass("inactive");
                },

                initialize: function()
                {
                    this.s_ViewScreen = $("#view-bargraph");
                    this.BarGraph.initialize();
                }
            },

            build: function(pageId, content)
            {
                this.TableViewScreen.hideScreen();
                this.BarGraphViewScreen.hideScreen();

                switch (pageId)
                {
                    case "table":
                        this.TableViewScreen.populateTable(content.columns);
                        this.TableViewScreen.showScreen();
                        break;

                    case "bargraph":
                        this.BarGraphViewScreen.populateBarGraph(content.barGraphOptions);
                        this.BarGraphViewScreen.showScreen();
                        break;
                }
            },

            initialize: function()
            {
                this.TableViewScreen.initialize();
                this.BarGraphViewScreen.initialize();
            }
        },

        initialize: function(tab, startFilterDate, endFilterDate)
        {
            var filterDateFormat = "mm/dd/yy";

            this.s_AlertDialog = $(".main.dialog-alert");
            this.s_AlertDialogContent = $(".main.dialog-content");
            this.s_Spinners = $(".spinner");
            this.s_ContentViews = $(".content");

            this.s_ActiveTab = $("#tab-"+tab);
            this.s_ActiveTab.addClass("active");

            this.s_FilterDateStart = $("#date-picker-start");
            this.s_FilterDateEnd = $("#date-picker-end");

            this.s_FilterDateStart.datepicker().datepicker("option", "dateFormat", filterDateFormat).datepicker("setDate", startFilterDate).on("change", function()
            {
                ViewController.s_FilterDateEnd.datepicker("option", "minDate", $.datepicker.parseDate("mm/dd/yy", this.value));
                ServiceController.filterContent(this.value, ViewController.getFilterDateEnd());
            });

            this.s_FilterDateEnd.datepicker().datepicker("option", "dateFormat", filterDateFormat).datepicker("setDate", endFilterDate).on("change", function()
            {
                ViewController.s_FilterDateStart.datepicker("option", "maxDate", $.datepicker.parseDate("mm/dd/yy", this.value));
                ServiceController.filterContent(ViewController.getFilterDateStart(), this.value);
            });

            this.ContentController.initialize();
        }
    };

    domWindow.ServiceController =
    {
        s_ActiveRequest: null,
        s_ActivePageIdentifier: null,

        ServiceRequest: function(requestCall, pageIdentifier, startFilterDate, endFilterDate)
        {
            this.m_PageIdentifier = pageIdentifier;
            this.m_FilterDateStart = startFilterDate;
            this.m_FilterDateEnd = endFilterDate;
            this.m_CallFunction = requestCall;
            this.m_SavedState = {};

            this.m_CompleteCallbackFunct = null;
            this.m_SuccessfulCallbackFunct = null;

            this.setCompletionCallback = function(callbackHandle) { this.m_CompleteCallbackFunct = callbackHandle; };
            this.setSuccessfulCallback = function(callbackHandle) { this.m_SuccessfulCallbackFunct = callbackHandle; };

            this.send = function()
            {
                $.ajax({
                    url: "?c=" + this.m_CallFunction + "&p=" + this.m_PageIdentifier + "&sf=" +
                    this.m_FilterDateStart.split('/').join("") + "&ef=" + this.m_FilterDateEnd.split('/').join("")
                }).always(this.m_CompleteCallbackFunct).done(this.m_SuccessfulCallbackFunct);
            };

            this.set = function(key, value)
            {
                this.m_SavedState[key] = value;
            };

            this.get = function(key)
            {
                if(this.m_SavedState.hasOwnProperty(key))
                    return this.m_SavedState[key];

                return null;
            };

            return this;
        },

        checkBusyState: function()
        {
            if (this.s_ActiveRequest != null)
            {
                ViewController.alert("Please wait. We are still busy with your last request.");
                return true;
            }

            return false;
        },

        loadContent: function(pageIdentifier, startFilterDate, endFilterDate, pushNavigation)
        {
            if (this.checkBusyState())
                return;

            var request = new this.ServiceRequest("content", pageIdentifier, startFilterDate, endFilterDate);

            request.setCompletionCallback(function(){
                ViewController.toggleLoading(false);
                ServiceController.s_ActiveRequest = null;
            });

            if (pushNavigation)
            {
                request.setSuccessfulCallback(function(data, textStatus, jqXHR){
                    ViewController.switchTab(data.pageId);
                    ServiceController.s_ActivePageIdentifier = data.pageId;
                    history.pushState({
                        "pageTitle":data.results.title
                    }, "", "?p="+data.pageId+"&sf="+startFilterDate.split('/').join("")+"&ef="+endFilterDate.split('/').join(""));

                    document.title = data.results.title;

                    ViewController.ContentController.build(data.pageId, data.results.content);
                });
            }
            else
            {
                request.setSuccessfulCallback(function(data, textStatus, jqXHR){
                    ServiceController.s_ActivePageIdentifier = data.pageId;
                    ViewController.ContentController.build(data.pageId, data.results.content);
                });
            }

            this.s_ActiveRequest = request;
            ViewController.toggleLoading(true);

            request.send();
        },

        filterContent: function(startFilterDate, endFilterDate)
        {
            if (this.checkBusyState())
                return;

            var request = new this.ServiceRequest("content", this.s_ActivePageIdentifier, startFilterDate, endFilterDate);

            request.setCompletionCallback(function(){
                ViewController.toggleLoading(false);
                ServiceController.s_ActiveRequest = null;
            });

            request.setSuccessfulCallback(function(data, textStatus, jqXHR){
                history.pushState({
                    "pageTitle":data.results.title
                }, "", "?p="+data.pageId+"&sf="+startFilterDate.split('/').join("")+"&ef="+endFilterDate.split('/').join(""));

                document.title = data.results.title;

                ViewController.ContentController.build(data.pageId, data.results.content);
            });

            this.s_ActiveRequest = request;
            ViewController.toggleLoading(true);

            request.send();
        },

        exportContent: function(startFilterDate, endFilterDate)
        {
            location.assign("?c=export&p=" + this.s_ActivePageIdentifier + "&sf=" +
            startFilterDate.split('/').join("") + "&ef=" + endFilterDate.split('/').join(""));
        }
    };

    $(domWindow.document).ready(function()
    {
        ViewController.initialize(Defaults.PageActiveTab, Defaults.PageFilterDateStart, Defaults.PageFilterDateEnd);

        $("#tab-table").click(function()
        {
            ServiceController.loadContent("table", ViewController.getFilterDateStart(), ViewController.getFilterDateEnd(), true);
        });

        $("#tab-bargraph").click(function()
        {
            ServiceController.loadContent("bargraph", ViewController.getFilterDateStart(), ViewController.getFilterDateEnd(), true);
        });

        $("#tab-export").click(function()
        {
            ServiceController.exportContent(ViewController.getFilterDateStart(), ViewController.getFilterDateEnd());
        });

        ServiceController.loadContent(Defaults.PageActiveTab, Defaults.PageFilterDateStart, Defaults.PageFilterDateEnd, false);
    });

})(window);