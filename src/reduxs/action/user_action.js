import * as TYPES from '../types';
export function userInfoAction(user_type, data) {
    return {
        type: TYPES.userInfo,
        data: data,
    }
}