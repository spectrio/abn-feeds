$(document).ready(function() {
	var player = videojs('video', {
		"controls": true,
		"autoplay": false,
		"preload": "auto"
	});
	player.playlist([{
	  sources: [{
	    src: 'assets/video/H_DS_CHEVROLET_ACCOLADES2.mp4',
	    type: 'video/mp4'
	  }]
	}, {
	  sources: [{
	    src: 'assets/video/H_DS_VW4_11071_TESTING.mp4',
	    type: 'video/mp4'
	  }]
	}, {
	  sources: [{
	    src: 'assets/video/H_DS_VW9_LEXUS_TESTING.mp4',
	    type: 'video/mp4'
	  }]
	}]);

	// Play through the playlist automatically.
	player.playlist.autoadvance(0);
	player.playlist.repeat(true);
	player.play();
});
