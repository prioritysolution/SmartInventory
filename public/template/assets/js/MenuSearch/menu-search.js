$(document).ready(function() {
    setTimeout(function() {
        var menuItems = [];

        function collectMenuItems() {
            menuItems = [];
            $('#sidebar-menu .submenu').each(function() {
                var parentName = $(this).find('> a > span').text().trim();
                var parentLink = $(this).find('> a').get(0);
                
                $(this).find('> ul > li > a').each(function() {
                    menuItems.push({
                        parent: parentName,
                        child: $(this).text().trim(),
                        link: this,
                        parentLink: parentLink
                    });
                });
            });
        }

        collectMenuItems();

        $('#menu-search').on('input', function() {
            var query = $(this).val().toLowerCase().trim();
            
            if (!query) {
                $('#search-results').hide();
                return;
            }

            var filtered = menuItems.filter(function(item) {
                return item.child.toLowerCase().includes(query) || 
                       item.parent.toLowerCase().includes(query);
            });

            if (filtered.length > 0) {
                var html = '';
                $.each(filtered, function(index, item) {
                    html += '<a href="javascript:void(0);" class="dropdown-item search-result-item" data-index="' + index + '">' +
                            '<div class="d-flex align-items-center">' +
                            '<i class="isax isax-arrow-right-3 me-2"></i>' +
                            '<div><div class="fw-semibold">' + item.child + '</div>' +
                            '<small class="text-muted">' + item.parent + '</small></div></div></a>';
                });
                $('#search-results').html(html).show();
            } else {
                $('#search-results').html('<div class="dropdown-item text-muted">No results found</div>').show();
            }
        });
$(document).on('click', '.search-result-item', function(e) {
    e.preventDefault();
    var index = $(this).data('index');
    var query = $('#menu-search').val().toLowerCase().trim();
    var filtered = menuItems.filter(function(item) {
        return item.child.toLowerCase().includes(query) || 
               item.parent.toLowerCase().includes(query);
    });
    
    if (filtered[index]) {
        var item = filtered[index];
        var href = $(item.link).attr('href');
        
        // Open submenu first
        if (!$(item.parentLink).hasClass('subdrop')) {
            $(item.parentLink).click();
        }
        
        // Then navigate after delay
        setTimeout(function() {
            item.link.scrollIntoView({ behavior: 'smooth', block: 'center' });
            $(item.link).addClass('active');
            
            if (href && href !== 'javascript:void(0);') {
                window.location.href = href;
            }
        }, 300);
    }
    
    $('#menu-search').val('');
    $('#search-results').hide();
});

    }, 500);
});
