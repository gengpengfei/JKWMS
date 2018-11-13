import { combineReducers } from 'redux';
import userInfo_reducer from './user_reducer';
export default combineReducers({
    userInfo: userInfo_reducer,
});