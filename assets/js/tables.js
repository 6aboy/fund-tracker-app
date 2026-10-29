document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('transactionsTable');
    if (!table) return;
    if (typeof DataTable === 'undefined') {
        const message = document.createElement('p');
        message.className = 'text-muted small';
        message.textContent = 'Table controls could not load. Check your connection and refresh.';
        table.before(message);
        return;
    }
    const exportOptions = {
        columns: ':not(.no-export)',
        modifier: { search: 'applied', order: 'applied', page: 'all', selected: null },
        escapeExcelFormula: true,
        format: { body: (data, row, column, node) => {
            if (column === 3) return node.getAttribute('data-order') ?? node.textContent.trim();
            if (column === 4) return node.getAttribute('data-order') ?? node.textContent.trim();
            return node.textContent.trim();
        } }
    };
    new DataTable(table, {
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[4, 'desc'], [0, 'desc']],
        scrollX: true,
        layout: {
            topStart: { buttons: [
                { extend: 'excelHtml5', text: 'Export Excel', title: 'Fund Tracker - Transactions', exportOptions },
                { extend: 'pdfHtml5', text: 'Export PDF', title: 'Fund Tracker - Transactions', orientation: 'landscape', pageSize: 'A4', exportOptions,
                    customize: doc => {
                        doc.defaultStyle.fontSize = 10;
                        doc.styles.tableHeader.fillColor = '#4f46e5';
                        doc.styles.tableHeader.color = '#ffffff';
                        doc.styles.title.color = '#18243b';
                        doc.footer = (page, pages) => ({ text: `Fund Tracker | Page ${page} of ${pages}`, alignment: 'right', margin: [40, 0], color: '#64748b', fontSize: 8 });
                    }
                }
            ] },
            topEnd: 'search', bottomStart: ['pageLength', 'info'], bottomEnd: 'paging'
        },
        columnDefs: [{ targets: 'no-export', orderable: false, searchable: false }],
        language: { search: '', searchPlaceholder: 'Search transactions...', emptyTable: 'No transactions yet. Add your first deposit or expense.' }
    });
    table.addEventListener('submit', event => {
        if (event.target.matches('.delete-transaction') && !confirm('Delete this transaction? This updates your balance and reports.')) event.preventDefault();
    });
});
