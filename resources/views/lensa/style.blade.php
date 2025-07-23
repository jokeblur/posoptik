<style>
    .card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }
    
    .card-primary {
        border-top: 3px solid #007bff;
    }
    
    .card-success {
        border-top: 3px solid #28a745;
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }
    
    .card-title {
        font-weight: 600;
        color: #495057;
    }
    
    .btn-tool {
        color: #6c757d;
    }
    
    .btn-tool:hover {
        color: #495057;
    }
    
    .table-responsive {
        border-radius: 0.25rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin-left: 0.125rem;
        border-radius: 0.25rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff;
        color: white !important;
        border: 1px solid #007bff;
    }
</style>
