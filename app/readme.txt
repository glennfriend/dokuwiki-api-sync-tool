Business        - 共用商業邏輯封裝, business layer
Controllers     - 傳入參數處理, 分派 view, 流程管理, 共用邏輯可放於 "Business"
Library         - 『完全』無耦合程式, 可以丟上 composer 的程式碼
Model           - Data Access Object
Shell           - 相同於 Controllers 的使用, 只使用在 command line 環境
Utility         - App Project 專用的程式, helper 請放置此處
CommandWrapApi  - 利用 command line 做資料交換方式的 API
