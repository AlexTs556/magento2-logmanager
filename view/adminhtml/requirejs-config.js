let config = {
    map: {
        '*': {
            'datatables': 'https://cdn.datatables.net/2.1.4/js/dataTables.min.js'
        }
    },
    shim: {
        'datatables': {
            deps: ['jquery']
        }
    }
};
