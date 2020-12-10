<template>
  <div class="hello">
    <h1>Dashboard example</h1>
    <div><button class="btn btn-primary" @click="callProcess('HelloWorld')">Call process</button></div>
    <div><button class="btn btn-primary" @click="startProcess('HelloWorld', 2)">Start process SE 2</button></div>
    <div v-for="task in tasks" :key="task.id">
      {{ task }}
      <button class="btn btn-sm btn-secondary" @click="openTask(task)">open</button>
    </div>
  </div>
</template>

<script>
import workflowMixin from './mixins/workflow';

export default {
  name: 'HelloWorld',
  mixins: [workflowMixin],
  props: {
    msg: String,
  },
  data() {
    return {
      tasks: [],
    };
  },
  methods: {
    loadTasks() {
      window.axios
        .post('process', {
          call: {
            method: 'tasks',
            params: {},
          },
        })
        .then(response => {
          this.$set(this, 'tasks', response.data.response);
        });
    },
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.loadTasks();
    });
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
h3 {
  margin: 40px 0 0;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  display: inline-block;
  margin: 0 10px;
}
a {
  color: #42b983;
}
</style>
