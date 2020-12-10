<template>
  <div class="hello">
    <h2>Hello Task</h2>
    <div><small>{{ workflowToken }}</small></div>
    <div><button class="btn btn-primary" @click="completeTask()">Complete Task</button></div>
    <div><button class="btn btn-primary" @click="cancelProcess()">Cancel process</button></div>
  </div>
</template>

<script>
import workflowMixin from './mixins/workflow';

export default {
  name: 'HelloTask',
  mixins: [workflowMixin],
  props: {
    msg: String,
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
