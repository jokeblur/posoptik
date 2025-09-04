// Mobile DataTables Optimization for POS Optik Melati

$(document).ready(function() {
    console.log('Mobile DataTables optimization loaded');
    
    // Function to initialize mobile-optimized DataTables
    function initMobileDataTable(selector, options = {}) {
        const defaultOptions = {
            responsive: true,
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [
                { targets: '_all', defaultContent: '-' }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                // Add mobile-friendly classes
                $(this).closest('.table-responsive').addClass('table-responsive-mobile');
                
                // Adjust column widths for mobile
                if ($(window).width() <= 768) {
                    $(this).find('th, td').css('white-space', 'nowrap');
                }
            },
            initComplete: function() {
                // Add scroll indicators
                addScrollIndicators($(this).closest('.table-responsive'));
            }
        };
        
        // Merge with custom options
        const finalOptions = $.extend(true, {}, defaultOptions, options);
        
        return $(selector).DataTable(finalOptions);
    }
    
    // Function to add scroll indicators
    function addScrollIndicators(container) {
        if (container.length === 0) return;
        
        // Remove existing indicators
        container.find('.scroll-indicator').remove();
        
        // Add left indicator
        container.append('<div class="scroll-indicator scroll-indicator-left"><i class="fa fa-chevron-left"></i></div>');
        
        // Add right indicator
        container.append('<div class="scroll-indicator scroll-indicator-right"><i class="fa fa-chevron-right"></i></div>');
        
        // Update indicators on scroll
        container.on('scroll', function() {
            const scrollLeft = $(this).scrollLeft();
            const scrollWidth = $(this)[0].scrollWidth;
            const clientWidth = $(this)[0].clientWidth;
            
            // Update left indicator
            if (scrollLeft > 0) {
                container.find('.scroll-indicator-left').addClass('active');
            } else {
                container.find('.scroll-indicator-left').removeClass('active');
            }
            
            // Update right indicator
            if (scrollLeft < scrollWidth - clientWidth - 1) {
                container.find('.scroll-indicator-right').addClass('active');
            } else {
                container.find('.scroll-indicator-right').removeClass('active');
            }
        });
        
        // Initial check
        container.trigger('scroll');
    }
    
    // Function to create mobile card view
    function createMobileCardView(table, data) {
        const container = table.closest('.table-responsive');
        const cardContainer = $('<div class="table-card-view-mobile"></div>');
        
        data.forEach((row, index) => {
            const card = $(`
                <div class="card">
                    <div class="card-header">
                        ${row[1] || 'Item ' + (index + 1)}
                    </div>
                    <div class="card-body">
                        ${createCardContent(row, table)}
                    </div>
                </div>
            `);
            cardContainer.append(card);
        });
        
        container.after(cardContainer);
    }
    
    function createCardContent(row, table) {
        const headers = table.find('thead th');
        let content = '';
        
        headers.each(function(index) {
            if (index === 0) return; // Skip checkbox column
            const headerText = $(this).text().trim();
            const cellValue = row[index] || '-';
            
            if (headerText && cellValue !== '-') {
                content += `
                    <div class="row">
                        <div class="col-sm-3">${headerText}:</div>
                        <div class="col-sm-9">${cellValue}</div>
                    </div>
                `;
            }
        });
        
        return content;
    }
    
    // Initialize all existing DataTables with mobile optimization
    $('.datatable, .table').each(function() {
        const $table = $(this);
        
        // Skip if already initialized
        if ($.fn.DataTable.isDataTable($table)) {
            return;
        }
        
        // Add mobile wrapper
        if (!$table.closest('.table-responsive').length) {
            $table.wrap('<div class="table-responsive table-responsive-mobile"></div>');
        }
        
        // Initialize with mobile options
        initMobileDataTable($table, {
            pageLength: $(window).width() <= 768 ? 5 : 10,
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Details for ' + data[1];
                        }
                    }),
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.hidden ?
                                '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                    '<td>' + col.title + ':' + '</td> ' +
                                    '<td>' + col.data + '</td>' +
                                '</tr>' :
                                '';
                        }).join('');
                        
                        return data ?
                            $('<table/>').append(data) :
                            false;
                    }
                }
            }
        });
    });
    
    // Handle window resize
    $(window).on('resize', function() {
        $('.dataTable').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().columns.adjust().responsive.recalc();
            }
        });
    });
    
    // Add mobile-specific CSS
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .scroll-indicator {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(164, 25, 61, 0.8);
                color: white;
                padding: 8px 4px;
                border-radius: 4px;
                z-index: 10;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            }
            
            .scroll-indicator-left {
                left: 5px;
            }
            
            .scroll-indicator-right {
                right: 5px;
            }
            
            .scroll-indicator.active {
                opacity: 1;
            }
            
            .table-responsive-mobile {
                position: relative;
            }
            
            @media (max-width: 768px) {
                .dataTables_wrapper .dataTables_length,
                .dataTables_wrapper .dataTables_filter {
                    margin-bottom: 10px;
                }
                
                .dataTables_wrapper .dataTables_length select,
                .dataTables_wrapper .dataTables_filter input {
                    width: 100%;
                    max-width: 200px;
                }
                
                .dataTables_wrapper .dataTables_info,
                .dataTables_wrapper .dataTables_paginate {
                    text-align: center;
                    margin-top: 10px;
                }
            }
        `)
        .appendTo('head');
});

// Global function to reinitialize tables after AJAX updates
window.reinitMobileTables = function() {
    $('.datatable, .table').each(function() {
        const $table = $(this);
        
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().destroy();
        }
        
        // Reinitialize
        $table.DataTable({
            responsive: true,
            pageLength: $(window).width() <= 768 ? 5 : 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [
                { targets: '_all', defaultContent: '-' }
            ],
            drawCallback: function() {
                $(this).closest('.table-responsive').addClass('table-responsive-mobile');
            }
        });
    });
};
