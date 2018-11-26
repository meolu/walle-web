module.exports = {
    path: 'config',

    getChildRoutes(partialNextState, cb) {
        require.ensure([], (require) => {
            cb(null, [
                require('./role'),
                require('./user'),
            ]);
        });
    },
};
