import React from 'react';
import ReactDOM from 'react-dom';

class App extends React.Component {
  render() {
    return (
     <div style={{textAlign: 'center'}}>
        <h1>Hello World</h1>
      </div>);
  }
}

console.log('hello');

ReactDOM.render(<App />, document.getElementById('root'));
