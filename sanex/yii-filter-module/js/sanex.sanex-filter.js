$(document).ready(function() {
	var filter = {};

	getCheckStateByUrlParams(); //get checkbox state from GET query

	$('.fltr-wrapper .fltr-check').click(function() {
		createUrl($(this));
		createFilter();
	});

    function getCheckStateByUrlParams() {
    	//get all GET params array from url
    	var urlParams;
		(window.onpopstate = function () {
		    var match,
		        pl     = /\+/g,
		        search = /([^&=]+)=?([^&]*)/g,
		        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
		        query  = decodeURIComponent(window.location.search.substring(1));

		    urlParams = {};
		    while (match = search.exec(query)) {
		    	if (urlParams[decode(match[1])]) {
		    		urlParams[decode(match[1])] += ','+decode(match[2]);
		    	} else {
		    		urlParams[decode(match[1])] = decode(match[2]);
		    	}
		    }   
		})();
		
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

		var json = JSON.stringify(filter);
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
	       	   $('.fltr-data-wrapper').children().remove();
	           $('.fltr-data-wrapper').html(data);
	       }
	    });
	}
});
