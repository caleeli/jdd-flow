
class Bpmn
{
  constructor(props){
    Object.assign(this, props);
    this.observers = [];
  }
  get $api() {
    return this.$owner.$api;
  }
  get $route() {
    return this.$owner.$route;
  }
  get $router() {
    return this.$owner.$router;
  }
  get $tokens() {
    return this.$owner.$api.process_tokens;
  }
  get $token() {
    return this.$owner.$api.process_token;
  }
  get $instances() {
    return this.$owner.$api.process_instances;
  }
  get $instance() {
    return this.$owner.$api.process_instance;
  }
  wrap(object) {
    const bpmn = this;
    return Object.assign(object, {
      thenRoute(element) {
        bpmn.listenOnce('TaskAssigned', (token) => {
          if (!element || element === token.element ) {
            bpmn.routeTo(token);
          }
        });
      },
    });
  }
  /**
   * Call a process from a BPMN process definition
   *
   * @param {*} definitions Bpmn file that contains the definitions
   * @param {*} data Initial data
   * @param {*} processId Process ID
   */
  call(definitions, data = {}, processId = null) {
    return this.wrap(this.$api.process_instance.call('call', { definitions, data, processId }));
  }
  /**
   * Complete an active token
   *
   * @param {*} data 
   * @param {*} tokenId 
   */
  complete(data, tokenId = this.$route.query.tokenId) {
    return this.wrap(this.$api.process_token[tokenId]
      .call('complete', { data }));
  }
  /**
   * Update data from a token
   *
   * @param {*} data 
   * @param {*} tokenId 
   */
  update(data, tokenId = this.$route.query.tokenId) {
    return this.$api.process_token[tokenId]
      .call('updateData', { data });
  }
  /**
   * Get the implementation route of a token
   *
   * @param {*} token 
   */
  route(token) {
    const start = token.attributes.implementation.indexOf('#');
    return start > -1 && {
      path: token.attributes.implementation.substr(start + 1),
      query: { tokenId: token.id },
    };
  }
  /**
   * Route to implementation of a token
   *
   * @param {*} token 
   */
  routeTo(token) {
    this.$router.push(this.route(token));
  }
  /**
   * Get data available for tokenId
   *
   * @param array variables 
   * @param object defaultData
   * @param int tokenId 
   */
  data(variables = ['*'], defaultData = {}, tokenId = this.$route.query.tokenId) {
    return this.$api.process_token[tokenId].rowCall('getData', {variables, default: defaultData}, defaultData);
  }
  /**
   * Get the result of a call process
   *
   * @param {*} process 
   * @param {*} data 
   */
  rowCall(bpmn, data = {}, processId = null) {
    return this.$api.process_instance.rowCall('call', { bpmn, data, processId });
  }
  /**
   * Get the result of a complete active token
   *
   * @param {*} token 
   * @param {*} data 
   */
  rowComplete(token, data) {
    return this.$api.process_instance[token.attributes.instance_id]
      .rowCall('complete', { tokenId: token.id, data });
  }
  /**
   * Dispatch an bpmn event
   *
   * @param {*} event 
   * @param {*} data 
   */
  dispatch(event, data) {
    this[event] = data;
    this.observers.forEach(observer => {
      if (observer.event === event) {
        observer.callback(data);
      }
    });
    this.observers = this.observers.filter(observer => !observer.once || observer.event !== event);
  }
  /**
   * Add a listener that is triggered once
   *
   * @param {*} event 
   * @param {*} callback 
   */
  listenOnce(event, callback) {
    this.observers.push({
      event,
      callback,
      once: true,
    });
  }
}

export default Bpmn;
