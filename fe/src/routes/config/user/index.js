module.exports = {
    path: 'user',

    getChildRoutes(partialNextState, cb) {
        require.ensure([], (require) => {
            cb(null, [
                require('./add'),
                require('./edit'),
                require('./list'),
            ]);
        });
    },
};
