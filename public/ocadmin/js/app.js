// Ocadmin Custom Scripts

$(document).ready(function() {
    // Toggle Sidebar
    $('#button-menu').on('click', function() {
        $('#column-left').toggleClass('show');
    });

    // Sidebar Navigation Toggle
    $('#column-left #menu .has-children > a').on('click', function(e) {
        var $parent = $(this).parent();
        var $submenu = $parent.find('> ul');

        if ($submenu.length) {
            e.preventDefault();

            // Toggle this menu
            $submenu.collapse('toggle');

            // Update aria-expanded
            var isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded);
        }
    });

    // Close sidebar on click outside (mobile)
    $(document).on('click', function(e) {
        if ($(window).width() < 992) {
            if (!$(e.target).closest('#column-left, #button-menu').length) {
                $('#column-left').removeClass('show');
            }
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
