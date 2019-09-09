import workflowMixin from './components/mixins/workflow';

window.workflowMixin = workflowMixin;

window.addEventListener('load', () => {
    // Register ../routes/* as routes
    const files = require.context('./routes/', true, /\.vue$/i);
    files.keys().map(key => {
        // Register component as route
        window.router.addRoutes([{ path: files(key).default.path, component: files(key).default, props: true }]);
    });
});

