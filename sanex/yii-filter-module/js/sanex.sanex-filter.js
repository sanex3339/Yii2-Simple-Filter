$(document).ready(function() {
	var filter = {};
	var json;

	getCheckStateByUrlParams(); //get checkbox state from GET query

	if (!SanexFilterAjax)
		createFilterUrls();

	$('.fltr-wrapper .fltr-check').click(function() {
		if (SanexFilterAjax)
			createUrl($(this));
		createFilter();
	});

    function getCheckStateByUrlParams() {
    	//get all GET params array from url
    	var urlParams = getQueryParameters(window.location.search.substring(1));
		
		delete urlParams['filter']; //delete "filter" GET param

		//find all params and his values
		//for each value find similar checkbox, and set class 'active' for him
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
		if (!elem.hasClass('active')) {
			url = $.query.SET('filter', '1').SET(category+'[]', elem.attr('value')).toString();
			elem.addClass('active');
		} else {
			url = $.query.REMOVE(category, elem.attr('value'));
			elem.removeClass('active');
		}
		window.history.pushState('', '', url);
    }

    //create json array with filter params
	function createFilter() {
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
				filter[category] = {
					properties: property
				};
			} else {
				delete filter[category]; //delete all empty values from filter object
			}
		});

		json = JSON.stringify(filter);
		sendFilter(json);
	}

	//send filter params to method actionShowDataPost() of FilterController.php file
	//success data - html data of ajax view
	function sendFilter(filter) {
		$.ajax({
	       url: '/sanex-filter-ajax/',
	       type: 'POST',
	       data: {
	       		_csrf: yii.getCsrfToken(),
	       		filter: filter		
	       },
           dataType: 'html',
	       success: function(data) {
	       		var wrapper = $('.fltr-data-wrapper');
	       	    wrapper.children().remove();
	            wrapper.html(data);
	            replaceUrls(wrapper);
	       }
	    });
	}

	function createFilterUrls()
	{
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

	function replaceUrls(elem)
	{
		elem.find('a').each(function(){
			var linkOldHref = $(this).attr('href');
			var linkGetParamsArray = getQueryParameters(linkOldHref.split('/').pop().substring(1));
			var getQuery = '?'+window.location.search.substring(1);
			var linkGetParams = '&'+linkOldHref.split('/').pop().substring(1);
			for (getParam in linkGetParamsArray) {
				if (getParameterByName(getParam)) {
					getQuery = $.query.REMOVE(getParam).toString();
				}
			}
			href = getQuery+linkGetParams;
			$(this).attr('href', href);
		});
	}
	
	function getQueryParameters(href) {
	    //get all GET params array from url
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

	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	        results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
});
