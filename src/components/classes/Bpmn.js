
class Bpmn
{
  constructor(props){
    Object.assign(this, props);
  }
  get $api() {
    return this.$owner.$api;
  }
  get $route() {
    return this.$owner.$route;
  }
  /**
   * Call a process from a BPMN process definition
   *
   * @param {*} bpmn Bpmn file
   * @param {*} data Initial data
   * @param {*} processId Process ID
   */
  call(bpmn, data = {}, processId = null) {
    return this.$api.process.call('call', { bpmn, data, processId });
  }
  /**
   * Complete an active token
   *
   * @param {*} data 
   * @param {*} tokenId 
   */
  complete(data, tokenId = this.$route.query.tokenId) {
    return this.$api.process_token[tokenId]
      .call('complete', { data });
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
  route(token) {
    const start = token.attributes.implementation.indexOf('#');
    return start > -1 && {
      path: token.attributes.implementation.substr(start + 1),
      query: { tokenId: token.id },
    };
  }
  /**
   * Get the result of a call process
   *
   * @param {*} process 
   * @param {*} data 
   */
  rowCall(bpmn, data = {}, processId = null) {
    return this.$api.process.rowCall('call', { bpmn, data, processId });
  }
  /**
   * Get the result of a complete active token
   *
   * @param {*} token 
   * @param {*} data 
   */
  rowComplete(token, data){
    return this.$api.process[token.attributes.process_id]
      .rowCall('complete', { tokenId: token.id, data });
  }
}

export default Bpmn;
