// get react from dependencies
import React from 'react';
import ReactDOM from 'react-dom';

import AppRoot from './AppRoot';

ReactDOM.render(
    React.createElement(AppRoot, {}),
    document.getElementById('app-root')
);