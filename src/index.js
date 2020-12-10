import WorkflowMixin from './components/mixins/workflow';

window.WorkflowMixin = WorkflowMixin;

window.addEventListener('load', () => {
  // Register ../routes/* as routes
  const files = require.context('./routes/', true, /\.vue$/i);
  files.keys().map(key => {
    // Register component as route
    window.router.addRoutes([{ path: files(key).default.path, component: files(key).default, props: true }]);
  });
});

