function loadInstagramFeed() {
    $.instagramFeed({
        'username': 'vw',
        'container': "#vw-instagram-feed",
        'display_profile': false,
        'display_biography': false,
        'display_gallery': true,
        'display_igtv': true,
        'callback': null,
        'styling': true,
        'items': 200,
        'items_per_row': 4,
        'margin': 1
    });

    $(document).on('click', '[data-toggle="lightbox"]', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).ekkoLightbox({
            alwaysShowClose: true,
            maxHeight: 950
        });
    });
}