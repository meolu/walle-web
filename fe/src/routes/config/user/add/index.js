const NProgress = require('nprogress');

module.exports = {
    path: 'add',

    getComponent(nextState, cb) {
        require.ensure([], (require) => {
            cb(null, require('components/page/UserDetailsPanel').default);
        }, 'userDetailsPanel');
    },

    onEnter: () => {
        NProgress.done();
    },

    onLeave: () => {
        NProgress.start();
    },
};
