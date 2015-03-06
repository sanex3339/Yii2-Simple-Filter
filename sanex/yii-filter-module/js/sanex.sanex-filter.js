$(document).ready(function() {
	var filter = {};
	var range = {};
	var json;
	var oldGetParams; //variable for storage old GET-params after new get params was applied by createUrl() method 

	getCheckStateByUrlParams(); //get checkbox state from GET query
	if (!SanexFilterAjax)
		createFilterUrls(); //enable url generation for filter checkboxes when ajax disabled

	$('.fltr-wrapper .fltr-check').click(function() {
		oldGetParams = window.location.search.substring(1);
		if (SanexFilterAjax)
			createUrl($(this)); //disable history url generation when ajax disabled
		createFilter();
	});

	//range slider
	$(function() {
		var fromId = $.query.GET('range_from');
		var toId = $.query.GET('range_to');
		var getRange = {}, getRangeFrom = {}, getRangeTo = {};

		//create getRange object from `range_from` and `range_to` GET-params 
		for (id in fromId) {
			getRangeFrom[id] = {'from': fromId[id]};
		}
		for (id in toId) {
			getRangeTo[id] = {'to': toId[id]};
		}
		getRange = $.extend(true, getRangeFrom, getRangeTo);

		//create range slider
		$('.fltr-range').each(function() {
			var category = $(this).parent().attr('id'); 
			var elem = $('#'+$(this).attr('id'));
			var initFrom = elem.data('range-from');
			var initTo = elem.data('range-to');
			var from, to;

			if (jQuery.isEmptyObject(getRange[category])) {
				from = initFrom;
				to = initTo;
			} else {
				from = getRange[category].from;
				to = getRange[category].to;
				if (typeof getRange[category].from == 'undefined')
					from = initFrom;
				if (typeof getRange[category].to == 'undefined')
					to = initTo;
			}

			$(elem).slider({
				range: true,
				min: initFrom,
				max: initTo,
				values: [from, to],
				slide: function(event, ui) {
					$('body').css('cursor', 'pointer'); //set cursor pointer for body while mousedown on slider
					$('#fltr-range-amount-'+category).html(ui.values[0]+" - " +ui.values[1]);
				},
				change: function(event, ui){
					$('body').css('cursor', 'default'); //set default cursor
					//put to url GET-params with range
					url = $.query.SET('filter', '1').SET('range_from['+category+']', $(elem).slider("values", 0)).SET('range_to['+category+']', $(elem).slider("values", 1)).toString();
					window.history.pushState('', '', url);
					//if ajax disabled - reload page after mouseup, else - create filter
					if (!SanexFilterAjax) {
						location.href = location.search;
					} else {
						createFilter();		
					}
				}
			});
			//set range slider values on load
			$('#fltr-range-amount-'+category).html($(elem).slider("values", 0)+" - "+$(elem).slider("values", 1));
		});
	});

    function getCheckStateByUrlParams() {
    	//get all GET params array from url
    	var urlParams = getQueryParameters(window.location.search.substring(1));
		delete urlParams['filter']; //delete "filter" GET param
		//find all params and his values
		for (params in urlParams) {
			var category = params.split('[', 1);
			var properties = urlParams[params];
			var element = $('.fltr-wrapper .fltr-cat#'+category);
			element.find('.fltr-check[value="'+properties+'"]').addClass('active');
		}
    }

    //create GET url with help of jquery.query-object.js
    function createUrl(elem) {
    	var category = elem.parent().attr('id');
		var url;
		$.query.parseNew(location.search, location.hash.split("?").length > 1 ? location.hash.split("?")[1] : "");
		if (!elem.hasClass('active')) {
			url = $.query.SET('filter', '1').SET(category+'[]', elem.attr('value')).toString();
			elem.addClass('active');
			window.history.pushState('', '', url);
		} else {
			url = $.query.REMOVE(category, elem.attr('value'));
			elem.removeClass('active');
			window.history.pushState('', '', url);
			oldGetParams = window.location.search.substring(1);
		}
		
    }

    //create json array with filter params
	function createFilter() {
		//checkboxes
		$('.fltr-wrapper .fltr-cat').each(function() {
			var array = [];
			var category = $(this).attr('id');
			//find all elements inside category
			$(this).find('.fltr-check.active').each(function(index) {
				array[index] = $(this).attr('value'); //for each found element put his value into array
			});
			var property = array.join(); //create string from array
			//Делаем новый объект, с ключом - имя категории, значение - другой элемент... 
			//...с ключом properties, значение - сформированная строка
			if (property.length > 0) {
				filter[category] = {properties: property};
			} else {
				delete filter[category]; //delete all empty values from filter object
			}
		});
		
		//range sliders
		$('.fltr-range').each(function(index) {
			var array = [];
			var category = $(this).parent().attr('id'); 
			var elem = $('#'+$(this).attr('id'));
			var from = $(elem).slider("values", 0);
			var to = $(elem).slider("values", 1);

			var property = from+'-'+to;
			range[category] = {range: property};
		});

		//combine, convert to json
		$.extend(true, filter, range); //combine range with filter object
		json = JSON.stringify(filter);
		sendFilter(json);
	}

	//send filter params to method actionShowDataPost() of FilterController.php file
	//success data - html data of ajax view
	function sendFilter(filter) {
		$.ajax({
			url: sanexFilterAjaxUrl+'?'+oldGetParams,
			type: 'POST',
			data: {_csrf: yii.getCsrfToken(), filter: filter},
			dataType: 'html',
			success: function(data) {
					var wrapper = $('.fltr-data-wrapper');
				    wrapper.children().remove();
			    wrapper.html(data);
			    replaceUrls(wrapper);
			}
	    });
	}

	//function generate href for all filter checkboxes based on current GET params and checkboxes values
	function createFilterUrls() {
		$('.fltr-wrapper .fltr-check').each(function(){
			var elem = $(this);
			var getQuery = window.location.search.substring(1);
			var category = elem.parent().attr('id');
			if (!elem.hasClass('active')) {
				var filterGetParameter = $.query.set('filter', '1').SET(category+'[]', elem.attr('value')).toString();
			} else {
				var filterGetParameter = $.query.remove(category, elem.attr('value'));
			}
			$(this).attr('href', filterGetParameter);
		});
	}

	//function for fix invalid href for all urls inside Ajax view with Pjax content
	function replaceUrls(elem) {
		elem.find('a:not(".sfCustomUrl")').each(function(){
			var linkOldHref = $(this).attr('href');
			var linkGetParamsArray = getQueryParameters(linkOldHref.split('/').pop().substring(1));
			var getQuery = '?'+window.location.search.substring(1);
			var linkGetParams = '&'+linkOldHref.split('?').pop();
			for (getParam in linkGetParamsArray) {
				if (getParameterByName(getParam)) {
					getQuery = $.query.REMOVE(getParam).toString();
				}
			}
			href = getQuery+linkGetParams;
			if (href.charAt(0) == '&') 
				href = href.replace('&','?');
			$(this).attr('href', href);
		});
	}
	
	//return all GET params from URL as array
	function getQueryParameters(href) {
    	var urlParams;
		(window.onpopstate = function () {
		    var match,
		        pl     = /\+/g,
		        search = /([^&=]+)=?([^&]*)/g,
		        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
		        query  = decodeURIComponent(href);
		    urlParams = {};
		    while (match = search.exec(query)) {
		    	if (urlParams[decode(match[1])]) {
		    		urlParams[decode(match[1])] += ','+decode(match[2]);
		    	} else {
		    		urlParams[decode(match[1])] = decode(match[2]);
		    	}
		    }   
		})();
		return urlParams;
	}

	//return GET param by name
	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	        results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
});
