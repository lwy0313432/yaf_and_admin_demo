const stageFilter = (stage) => {
        if (!stage) return ''
        let str = "";
        switch (stage) {
            case "2":
                str = "启用";
                break;
            case "-1":
                str = "禁用";
                break;
            default:
                str = '';
                break;
        }
        return str;
    }
    /**
     * 将分转化为元
     * 
     * @param {Number} money 
     * @returns {Number}
     */
const centToYuanFilter = (money) => {
    if (!money) return '0';
    return money / 100
}
export default {
    stageFilter,
    centToYuanFilter
}