import Bpmn from '../classes/Bpmn';

export default {
  data() {
    return {
      socketListeners: [],
      bpmn: new Bpmn({
        $owner: this,
        // Events
        NewProcess: null,
      }),
    };
  },
  methods: {
    addSocketListener(channel, event, callback) {
      this.socketListeners.push({
        channel,
        event,
      });
      window.Echo.private(channel).listen(
        event,
        callback,
      );
    },
    listenBpmn(callback, instance = this.$route.query.instance, token = this.$route.query.token) {
      const channel = `Process.${instance}.Token.${token}`;
      this.addSocketListener(channel, '.ElementConsole', callback);
    },
    cleanSocketListeners() {
      // Stop registered socket listeners 
      this.socketListeners.forEach(element => {
        window.Echo.private(
          element.channel
        ).stopListening(element.event);
      });
    },
  },
  mounted() {
    this.addSocketListener('Bpmn', '.NewProcess', (data) => {
      this.bpmn.NewProcess = data;
    });
  },
};
