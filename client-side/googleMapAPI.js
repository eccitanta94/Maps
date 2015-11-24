/**
 * Copyright (c) 2015 Petr Olišar (http://olisar.eu)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

var GoogleMap = GoogleMap || {};

GoogleMap = function (element) {
	this.element = element;
	this.map;
	this.basePosition;
	this.preparedEvents = [];
	this.directionsDisplay = [];
	this.directionsService;
	this.markers = [];
	this.waypoints = [];
	this.travelmode;
	this.options = {};
	this.basePath;
	this.boundsProperty;
	this.markersCluster = new Array();
	this.URL = "";
	this.URLWaypoints = "";
	this.indexedMarkers = {};
	this.indexedWaypoints = {};
	this.allowColors = new Array('green', 'purple', 'yellow', 'blue', 'orange', 'red');
	this.init();

	WayPoint = function () {
		this.identifier;
		this.position;
		this.distance;
		this.duration;
		this.inicialized = false;
	};

};

GoogleMap.prototype = {

	constructor: GoogleMap,

	init: function () {
		this.setProperties();
		return this;
	},

	setProperties: function () {
		var properties = JSON.parse(this.element.dataset.map);

		this.options.position = properties.position;
		this.options.proportions = [properties.width, properties.height];
		this.options.zoom = properties.zoom;
		this.options.type = properties.type;
		this.options.scrollable = properties.scrollable;
		this.options.key = properties.key;
		this.options.bound = properties.bound;
		this.options.cluster = properties.cluster;
		this.basePath = this.element.dataset.basepath;
		this.URL = this.element.dataset.markersfallback;
		this.URLWaypoints = this.element.dataset.waypointsfallback;
		this.waypointLimit = this.element.dataset.waypointlimit;
		this.preserveViewport = this.element.dataset.preserveviewport;
		return this;
	},

	initialize: function () {
		var base = this;

		base.doBounds('init');

		base.basePosition = new google.maps.LatLng(base.options.position[0], base.options.position[1]);

		var mapOptions = {
			center: base.basePosition,
			zoom: base.options.zoom,
			mapTypeId: google.maps.MapTypeId[base.options.type],
			scrollwheel: base.options.scrollable
		};

		// Display a map on the page
		base.map = new google.maps.Map(base.element, mapOptions);
		base.map.setTilt(45);
	},

	loadMarkers: function () {
		var base = this;
		this.clearMarkers();

		var request = new XMLHttpRequest();
		request.open('GET', base.URL, true);

		request.onload = function () {
			if (request.status >= 200 && request.status < 400) {
				// Success!
				var data = JSON.parse(request.responseText);
				base.insertMarkers(data);
			} else {
				// We reached our target server, but it returned an error
				console.log('We reached our target server, but it returned an error');
			}
		};

		request.onerror = function () {
			// There was a connection error of some sort
			console.log('There was a connection error of some sort');
		};

		request.send();
	},

	doIndexMarker: function (option, marker) {
		var base = this;

		if (option['identifier']) {
			base.indexedMarkers[option['identifier']] = marker;
			marker.identifier = option['identifier'];
		}
	},

	doIndexWaypoint: function (option, waypoint) {
		var base = this;

		if (option['identifier']) {
			base.indexedWaypoints[option['identifier']] = waypoint;
			waypoint.identifier = option['identifier'];
		}
	},

	resetWaypoints: function () {
		var base = this;

		base.waypoints = [];

		base.directionsDisplay.forEach(function (directionsDisplay, i) {
			directionsDisplay.setMap(null);
		});

		base.directionsDisplay = [];
	},

	drawDirections: function (callback) {
		var base = this;


		//Reset displays and waypoint
		base.resetWaypoints();

		var request = new XMLHttpRequest();
		request.open('GET', base.URLWaypoints, true);

		request.onload = function () {
			if (request.status >= 200 && request.status < 400) {
				// Success!
				var data = JSON.parse(request.responseText);
				base.insertWaypoints(data);
				base.processDirection(callback);
			} else {
				// We reached our target server, but it returned an error
				console.log('We reached our target server, but it returned an error');
			}
		};

		request.onerror = function () {
			// There was a connection error of some sort
			console.log('There was a connection error of some sort');
		};

		request.send();

	},

	processDirection: function (callback) {
		var base = this;

		if (base.waypoints.length < 1) {
			return;
		}

		base.directionsService = new google.maps.DirectionsService();

		var start = base.basePosition;
		var request_completed = 0;
		var request_need = 0;

		if (base.waypointLimit >= base.waypoints.length) {
			request_need = 1;
		} else {
			request_need = Math.ceil(base.waypoints.length / base.waypointLimit);
		}

		base.sendWaypointRequest(start, base.waypoints, null, function () {
			request_completed++;
			if (typeof callback === 'function' && request_completed >= request_need) {
				callback();
			}
		});
	},

	sendWaypointRequest: function (start, waypoints, index, callback) {
		var base = this;

		if (!index) {
			index = 0;
		}

		var displayIndex = base.directionsDisplay.push(new google.maps.DirectionsRenderer({
				suppressMarkers: true,
				preserveViewport: base.preserveViewport
			})) - 1;

		base.directionsDisplay[displayIndex].setMap(base.map);

		var waypoints_to_send = [];

		for (i = 0; i < base.waypointLimit; i++) {

			if (typeof waypoints[index] === 'undefined') {
				break;
			}

			waypoints_to_send.push(waypoints[index]);
			index++;
		}

		if (index < base.waypoints.length) {
			var end = waypoints_to_send[(waypoints_to_send.length - 1)].position;
		} else {
			var end = base.basePosition;
		}

		var positions_to_send = [];
		waypoints_to_send.forEach(function (waypoint, i) {
			positions_to_send[i] = {
				location: waypoint.position,
				stopover: true
			};
		});


		var request = {
			origin: start,
			destination: end,
			waypoints: positions_to_send,
			optimizeWaypoints: false,
			travelMode: google.maps.TravelMode[base.travelmode]
		};


		base.directionsService.route(request, function (response, status) {

			if (status == google.maps.DirectionsStatus.OK) {
				base.directionsDisplay[displayIndex].setDirections(response);
				var legs = response.routes[0].legs;

				legs.forEach(function (leg, i) {
					if (typeof waypoints_to_send[i] !== 'undefined') {
						waypoints_to_send[i].inicialized = true;
						waypoints_to_send[i].distance = leg.distance.value;
						waypoints_to_send[i].duration = leg.duration.value;
					}
				});

				if (typeof callback === 'function') {
					callback();
				}
			}
		});

		if (index < base.waypoints.length) {
			base.sendWaypointRequest(end, waypoints, index, callback);
		}

	},

	insertMarkers: function (markers) {
		var base = this;

		markers.forEach(function (item, i) {
			var marker,
				position = new google.maps.LatLng(markers[i]['position'][0], markers[i]['position'][1]);
			base.doBounds('fill', position);

			marker = new google.maps.Marker({
				position: position,
				map: base.map,
				title: (("title" in markers[i]) ? markers[i]['title'] : null)
			});

			base.doPreparedEvent(marker);

			marker.setAnimation(base.doAdmination(item));

			base.doColor(item, marker);

			base.doIcon(item, marker);

			base.doIndexMarker(item, marker);

			if (base.options.cluster) {
				base.markersCluster.push(marker);
			}

			base.markers.push(marker);

		});

		base.doBounds('fit');


		if (base.options.cluster) {
			if (typeof MarkerClusterer != 'undefined') {
				new MarkerClusterer(base.map, base.markersCluster);
			} else {
				throw 'MarkerClusterer is not loaded! Please use markerclusterer.js from client-side folder';
			}
		}
	},

	getMarkerByIdentifier: function (identifier) {
		var base = this;
		return base.indexedMarkers[identifier];
	},

	getWaypointByIdentifier: function (identifier) {
		var base = this;
		return base.indexedWaypoints[identifier];
	},

	insertWaypoints: function (waypoints) {
		var base = this;

		this.travelmode = waypoints.direction.travelmode;

		waypoints.waypoints.forEach(function (item, i) {
			var position = new google.maps.LatLng(item['position'][0], item['position'][1]);
			var waypoint = new WayPoint();

			base.doIndexWaypoint(item, waypoint);

			waypoint.position = position;
			base.waypoints.push(waypoint);
		});
	},

	doPreparedEvent: function (marker) {
		$.each(this.preparedEvents, function (index, event) {
			google.maps.event.addListener(marker, event['event'], function () {
				event['callback'](marker);
			});
		})
	},

	clearMarkers: function () {
		var base = this;
		for (var i = 0; i < base.markers.length; i++) {
			base.markers[i].setMap(null);
		}
		base.markers.length = 0;
		base.markers = [];
	},

	doBounds: function (functionName, position) {
		var base = this;
		if (base.options.bound) {
			var fn = {
				init: function () {
					base.boundsProperty = new google.maps.LatLngBounds();
				},
				fill: function () {
					base.boundsProperty.extend(position);
				},
				fit: function () {
					base.map.fitBounds(base.boundsProperty);
				}
			};
			fn[functionName];
		}
	},

	doAdmination: function (marker) {
		var animation;
		if ("animation" in marker) {
			animation = google.maps.Animation[marker.animation];
		}

		return animation;
	},

	bounceToogle: function (marker) {
		if (marker.getAnimation()) {
			marker.setAnimation(null);
		} else {
			marker.setAnimation(google.maps.Animation.BOUNCE);
		}
	},

	doMessage: function (message, marker) {
		var base = this;
		var infoWindow = new google.maps.InfoWindow();

		infoWindow.setContent('<div>' + message + '</div>');
		infoWindow.open(base.map, marker);
	},

	prepareEventForInsertedMarkers: function (event, callback) {
		this.preparedEvents.push({
			event: event,
			callback: callback
		});
	},

	doProportions: function () {
		this.element.style.width = this.options.proportions[0];
		this.element.style.height = this.options.proportions[1];
	},

	doColor: function (option, marker) {
		var base = this;

		if ("color" in option && base.allowColors.indexOf(option['color']) >= 0) {
			marker.setIcon('http://maps.google.com/mapfiles/ms/icons/' + option['color'] + '-dot.png');
		}
	},

	doIcon: function (option, marker) {
		if ("icon" in option) {
			var host = "http://" + window.location.hostname;
			if (option['icon'] instanceof Object) {
				var icon = {
					url: host + this.basePath + '/' + option['icon']['url']
				};

				if (option['icon']['size'] !== null) {
					icon['size'] = new google.maps.Size(option['icon']['size'][0], option['icon']['size'][1]);
				}

				if (option['icon']['anchor'] !== null) {
					icon['size'] = new new google.maps.Point(option['icon']['anchor'][0], option['icon']['anchor'][1]);
				}

				if (option['icon']['origin'] !== null) {
					icon['size'] = new new google.maps.Point(option['icon']['orign'][0], option['icon']['origin'][1]);
				}

			} else {
				var icon = {
					url: host + this.basePath + '/' + option['icon']
				};
			}

			marker.setIcon(icon);
		}
	},

	getKey: function () {
		return this.options.key;
	},

	removeAllBounce: function (marker) {
		map.markers.forEach(function (item, i) {
			item.setAnimation(null);
		});
	}
};

var map;
Array.prototype.forEach.call(document.getElementsByClassName('googleMapAPI'), function (el, i) {
	map = new GoogleMap(el);
	map.doProportions();
	if (typeof google === "undefined") {
		loadScript();
	} else {
		map.initialize();
	}
});


function loadScript() {
	var script = document.createElement('script');
	script.type = 'text/javascript';
	var key = (map.getKey() !== null ? "&key=" + map.getKey() : '');
	script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&' +
		'callback=map.initialize' + key;
	document.body.appendChild(script);
}