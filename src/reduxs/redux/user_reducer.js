import * as TYPES from '../types'
export default function user_reducer(state = initUserInfo, action) {
    switch (action.type) {
        case TYPES.userInfo:
            return {
                ...state,
                userInfo: action.data,
            }
        default:
            return {
                ...state,
            }
    }
}
const initUserInfo = {}
