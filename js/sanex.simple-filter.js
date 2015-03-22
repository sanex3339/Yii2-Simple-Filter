$(document).ready(function() {
	var filter = {};
	var json;
	var oldGetParams; //variable for storage old GET-params after new get params was applied by createUrl() method 

	getCheckStateByUrlParams(); //get checkbox state from GET query
	if (!SimpleFilterAjax)
		createFilterUrls(); //enable url generation for filter checkboxes when ajax disabled

	$('.fltr-wrapper .fltr-check').click(function() {
		oldGetParams = window.location.search.substring(1);
		if (SimpleFilterAjax)
			createUrl($(this)); //disable history url generation when ajax disabled
		createFilter();
	});

    function getCheckStateByUrlParams() {
    	//get all GET params array from url
    	var urlParams = getQueryParameters(window.location.search.substring(1));
		delete urlParams['filter']; //delete "filter" GET param
		//find all params and his values
		for (params in urlParams) {
			var category = params.split('[', 1);
			var properties = urlParams[params];
			var element = $('.fltr-wrapper .fltr-cat[data-property="'+category+'"]');
			element.find('.fltr-check[value="'+properties+'"]').addClass('active');
		}
    }

    //create GET url with help of jquery.query-object.js
    function createUrl(elem) {
    	var category = elem.parent().data('property');
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
		var counter = 0;
		var prevCategory;
		$('.fltr-wrapper .fltr-cat').each(function() {
			var array = [];
			var category = $(this).data('property');

			if (category != prevCategory) counter = 0;

			//find all elements inside category
			$(this).find('.fltr-check.active').each(function(index) {
				array[index] = $(this).attr('value'); //for each found element put his value into array
			});
			var property = array.join(); //create string from array
			//Делаем новый объект, с ключом - имя категории, значение - другой элемент... 
			//...с ключом properties, значение - сформированная строка
			if (property.length > 0) {
				filter[category+'['+counter+']'] = {properties: property};
			} else {
				delete filter[category+'['+counter+']']; //delete all empty values from filter object
			}
			counter = counter + 1;
			prevCategory = category;
		});
		json = JSON.stringify(filter);
		sendFilter(json);
	}

	//send filter params to method actionShowDataPost() of FilterController.php file
	//success data - html data of ajax view
	function sendFilter(filter) {
		$.ajax({
			url: SimpleFilterAjaxUrl+'?'+oldGetParams,
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
	function createFilterUrls()
	{
		$('.fltr-wrapper .fltr-check').each(function(){
			var elem = $(this);
			var getQuery = window.location.search.substring(1);
			var category = elem.parent().data('property');
			if (!elem.hasClass('active')) {
				var filterGetParameter = $.query.set('filter', '1').SET(category+'[]', elem.attr('value')).toString();
			} else {
				var filterGetParameter = $.query.remove(category, elem.attr('value'));
			}
			$(this).attr('href', filterGetParameter);
		});
	}

	//function for fix invalid href for all urls inside Ajax view with Pjax content
	function replaceUrls(elem)
	{
		elem.find('a:not(".sfCustomUrl")').each(function(){
			var linkOldHref = $(this).attr('href');
			var linkGetParamsArray = getQueryParameters(linkOldHref.split('/').pop().substring(1));
			var getQuery = '?'+window.location.search.substring(1);
			var linkGetParams = '&'+linkOldHref.split('?').pop();
			var href;
			$.query.parseNew(location.search, location.hash.split("?").length > 1 ? location.hash.split("?")[1] : "");
			for (getParam in linkGetParamsArray) {
				href = $.query.SET(getParam, linkGetParamsArray[getParam]).toString();	
			}
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
});
