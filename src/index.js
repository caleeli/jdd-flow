import WorkflowMixin from './components/mixins/workflow';
import Bpmn from './components/classes/Bpmn';

window.WorkflowMixin = WorkflowMixin;
window.Bpmn = Bpmn;

window.addEventListener('load', () => {
  // Register ../routes/* as routes
  const files = require.context('./routes/', true, /\.vue$/i);
  files.keys().map(key => {
    // Register component as route
    window.router.addRoute({ path: files(key).default.path, component: files(key).default, props: true });
  });
});

