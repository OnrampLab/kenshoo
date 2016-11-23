
function getConfig()
{
    /*
        ads id 來源
            登入之後到 ads 頁面 https://ads.pinterest.com/
            "Export data" Button 點擊後會下載 CSV
            利用 browser developer tool 查看該 CSV 來源, 類似
                https://ads.pinterest.com/analytics/advertiser/999999999999/export/?start_date=2015-01-01&end_date=2015-01-31
            該 999999999999 就是 ads id
    */
    return {
        account:  "pinterest帳號",
        password: "pinterest密碼",
        adsId:    "Ads ID"
    };
}

