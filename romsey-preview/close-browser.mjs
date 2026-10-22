import {connect} from './browser.mjs';
const b=await connect();
await b.call('Browser.close');
b.close();
