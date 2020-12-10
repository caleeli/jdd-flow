import Bpmn from '../classes/Bpmn';

export default {
  beforeCreate() {
    this.bpmn = new Bpmn({
      $owner: this,
      // Events
      NewProcess: null,
      TaskAssigned: null,
    });
  },
  data() {
    return {
      socketListeners: [],
      bpmn: this.bpmn,
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
      this.bpmn.dispatch('NewProcess', data);
    });
    const userId = (this.$root.user && this.$root.user.id) || (window.userId);
    if (userId) {
      this.addSocketListener(`User.${userId}`, '.TaskAssigned', (data) => {
        this.bpmn.dispatch('TaskAssigned', data);
      });
    }
  },
};
