<template>
  <panel name icon="fas fa-book-reader" class="panel-success">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th scope="col"/>
          <th scope="col">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(task, i) in tasks" :key="i">
          <td>
            <router-link :to="{ path: task.path, query: task.token }">{{
              task.name
            }}</router-link>
          </td>
          <td>{{ task.status }}</td>
        </tr>
      </tbody>
    </table>
  </panel>
</template>

<script>
import workflowMixin from '../components/mixins/workflow';

export default {
  name: 'NextStep',
  path: '/process/next',
  mixins: [workflowMixin],
  data() {
    return {
      tasks: [],
    };
  },
  methods: {
    updateTasks(tasks) {
      const actives = [];
      tasks.forEach((task) => {
        task.status === 'ACTIVE' ? actives.push(task) : null;
      });
      if (actives.length === 1 && actives[0].path) {
        this.openTask(tasks[0]);
      } else if (tasks.length === 0) {
        this.gotoDashboard();
      } else {
        this.$set(this, 'tasks', tasks);
      }
    },
    loadTasks() {
      this.processTasks(this.workflowToken).then((response) => {
        const tasks = response.data.response;
        this.updateTasks(tasks);
      });
    },
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      vm.loadTasks();
    });
  },
  mounted() {
    const instance = this.workflowToken.instance;
    const channel = `Process.${instance}`;
    this.addSocketListener(channel, '.ProcessUpdated', (event) => {
      this.updateTasks(event.tasks);
    });
  },
};
</script>
