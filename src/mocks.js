const process = {
    lastTokenId: 0,
    tokens: [],
    call({ processUrl, data }) {
        processUrl; data;
        this.createToken();
        return { id: 1, attributes: {} };
    },
    start({ processUrl, start, data }) {
        processUrl; start; data;
        this.createToken();
        return { id: 1, attributes: {} };
    },
    tasks() {
        return this.tokens;
    },
    complete({ token, data }) {
        token; data;
        const index = this.tokens.findIndex((item) => item.token.token === token);
        index > -1 ? this.tokens.splice(index, 1) : null;
        this.createToken();
        this.createToken();
    },
    cancel() {
        this.tokens.splice(0);
    },
    createToken() {
        this.tokens.push({ path: '/task', token: { instance: 1, token: ++this.lastTokenId } });
    }
};
const mockedRoutes = {
    'process': function (body) {
        if (!(process[body.call.method] instanceof Function)) {
            throw "Method not found: " + body.call.method;
        }
        return { success: true, response: process[body.call.method](body.call.params) };
    },
    'process/1': function (body) {
        if (!(process[body.call.method] instanceof Function)) {
            throw "Method not found: " + body.call.method;
        }
        return { success: true, response: process[body.call.method](body.call.params) };
    },
};

export default {
    post: function (path, body) {
        return new Promise((resolve) => {
            //console.log('POST', path, body);
            if (mockedRoutes[path] instanceof Function) {
                const data = mockedRoutes[path](body);
                //console.log(data);
                resolve({ data });
            } else {
                throw "NOT FOUND " + path;
            }
        });
    }
};
