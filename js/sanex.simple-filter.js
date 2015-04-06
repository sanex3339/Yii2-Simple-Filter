new function() {
    var simpleFilter = function () {
        var filterObject = {
            var: {
                dataWrapper: $('.fltr-data-wrapper'),
                filter: {},
                linksDefaultGetParams: [],
                oldGetParams: []
            },
            init: function () {
                this.events();
                this.getCheckboxState();
                if (!SimpleFilterAjax)
                    this.setFilterUrls();
                this.getDefaultLinksGetParams(this.var.dataWrapper);
            },
            events: function () {
                var self = this;
                $(document).ready(function () {
                    $('.fltr-wrapper .fltr-check').click(function () {
                        self.oldGetParams = window.location.search.substring(1);
                        if (SimpleFilterAjax)
                            self.setQueryUrl($(this));
                        self.setFilterData();
                    });
                });
            },
            getCheckboxState: function () {
                var urlParams = this.getQueryParameters(window.location.search.substring(1));
                delete urlParams['filter'];
                for (params in urlParams) {
                    var category = params.split('[', 1),
                        properties = urlParams[params],
                        element = $('.fltr-wrapper .fltr-cat[data-property="' + category + '"]');
                    element.find('.fltr-check[value="' + properties + '"]').addClass('active');
                }
            },
            getQueryParameters: function (href) {
                var urlParams;
                (window.onpopstate = function () {
                    var match,
                        pl = /\+/g,
                        search = /([^&=]+)=?([^&]*)/g,
                        decode = function (s) {
                            return decodeURIComponent(s.replace(pl, " "));
                        },
                        query = decodeURIComponent(href);
                    urlParams = {};
                    while (match = search.exec(query)) {
                        if (urlParams[decode(match[1])]) {
                            urlParams[decode(match[1])] += ',' + decode(match[2]);
                        } else {
                            urlParams[decode(match[1])] = decode(match[2]);
                        }
                    }
                })();
                return urlParams;
            },
            getDefaultLinksGetParams: function (elem) {
                var self = this;
                elem.find('a:not(".sfCustomUrl")').each(function (index) {
                    self.var.linksDefaultGetParams[index] = self.getQueryParameters($(this).attr('href').split('/').pop().split('?').pop());
                });
            },
            replaceUrlForLinks: function (elem) {
                var self = this;
                elem.find('a:not(".sfCustomUrl")').each(function (index) {
                    $.query.parseNew(location.search, location.hash.split("?").length > 1 ? location.hash.split("?")[1] : "");
                    for (getParam in self.var.linksDefaultGetParams[index]) {
                        href = $.query.SET(getParam, self.var.linksDefaultGetParams[index][getParam]).toString();
                    }
                    if (href.charAt(0) == '&')
                        href = href.replace('&','?');
                    $(this).attr('href', href);
                });
            },
            sendFilter: function (filterJsonFormat) {
                var self = this;
                $.ajax({
                    url: SimpleFilterAjaxUrl + '?' + this.var.oldGetParams,
                    type: 'POST',
                    data: {_csrf: yii.getCsrfToken(), filter: filterJsonFormat},
                    dataType: 'html',
                    success: function (data) {
                        self.var.dataWrapper.children().remove();
                        self.var.dataWrapper.html(data);
                        self.replaceUrlForLinks(self.var.dataWrapper);
                    }
                });
            },
            setFilterData: function () {
                var counter = 0,
                    prevCategory,
                    self = this;
                $('.fltr-wrapper .fltr-cat').each(function () {
                    var array = [],
                        category = $(this).data('property');
                    if (category != prevCategory)
                        counter = 0;
                    $(this).find('.fltr-check.active').each(function (index) {
                        array[index] = $(this).attr('value');
                    });
                    var property = array.join();
                    if (property.length > 0) {
                        self.var.filter[category + '[' + counter + ']'] = {properties: property};
                    } else {
                        delete self.var.filter[category + '[' + counter + ']'];
                    }
                    counter++;
                    prevCategory = category;
                });
                var filterJsonFormat = JSON.stringify(this.var.filter);
                this.sendFilter(filterJsonFormat);
            },
            setFilterUrls: function () {
                $('.fltr-wrapper .fltr-check').each(function (){
                    var elem = $(this),
                        category = elem.parent().data('property'),
                        filterGetParameter,
                        getQuery = window.location.search.substring(1);
                    if (!elem.hasClass('active')) {
                        filterGetParameter = $.query.set('filter', '1').SET(category + '[]', elem.attr('value')).toString();
                    } else {
                        filterGetParameter = $.query.remove(category, elem.attr('value'));
                    }
                    $(this).attr('href', filterGetParameter);
                });
            },
            setQueryUrl: function (elem) {
                var category = elem.parent().data('property'),
                    url;
                $.query.parseNew(location.search, location.hash.split("?").length > 1 ? location.hash.split("?")[1] : "");
                if (!elem.hasClass('active')) {
                    url = $.query.SET('filter', '1').SET(category + '[]', elem.attr('value')).toString();
                    elem.addClass('active');
                    window.history.pushState('', '', url);
                } else {
                    url = $.query.REMOVE(category, elem.attr('value'));
                    elem.removeClass('active');
                    window.history.pushState('', '', url);
                    oldGetParams = window.location.search.substring(1);
                }
            }
        };
        filterObject.init();
    };
    return simpleFilter();
}();