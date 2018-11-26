import reqwest from 'reqwest';
import { message } from 'antd';

export default (options) => new Promise((resolve, reject) => {
    options.url = `/api${options.url}`;
    return reqwest(options).then(data => {
        const { code } = data;
        if (code !== 0) {
            return message.error(data.message[Object.keys(data.message)[0]]);
        }
        resolve(data);
    }).catch((err) => {
        message.error('请求失败，请检查网络设置');
        reject(err);
    });
});
