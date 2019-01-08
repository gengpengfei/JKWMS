import React, { Component } from 'react';
import { Route, Switch, BrowserRouter } from 'react-router-dom'
import Login from './containers/login/login'
import Index from './containers/home/index'
import Home from './containers/home/home'
import NoMatch from './components/layout/nomatch'
import Goods from './containers/shop/goods'
import GoodsBindSupplier from './containers/shop/goodsBindSupplier'
import GoodsType from './containers/shop/goodsType';
import GoodsTypeAdd from './containers/shop/goodsTypeAdd';
import GoodsTypeAddChild from './containers/shop/goodsTypeAddChild';
import GoodsTypeEdit from './containers/shop/goodsTypeEdit';
import ProExclude from './containers/shop/proExclude';
import ProExcludeAdd from './containers/shop/proExcludeAdd';
import ProExcludeEdit from './containers/shop/proExcludeEdit';
import ProFruit from './containers/shop/proFruit';
import ProFruitAdd from './containers/shop/proFruitAdd';
import ProFruitEdit from './containers/shop/proFruitEdit';
import ProOffline from './containers/shop/proOffline';
import Vendor from './containers/vendor/vendor'
import VendorAdd from './containers/vendor/vendorAdd'
import VendorEdit from './containers/vendor/vendorEdit'
import VendorBindProduct from './containers/vendor/vendorBindProduct'
import Warehouse from './containers/warehouse/warehouse'
import WarehouseAdd from './containers/warehouse/warehouseAdd'
import WarehouseEdit from './containers/warehouse/warehouseEdit'
import WarehouseArea from './containers/warehouse/warehouseArea'
import WarehouseAreaAdd from './containers/warehouse/warehouseAreaAdd'
import WarehouseAreaEdit from './containers/warehouse/warehouseAreaEdit'
import WarehouseRowShelf from './containers/warehouse/warehouseRowShelf'
import WarehouseRowShelfAdd from './containers/warehouse/warehouseRowShelfAdd'
import WarehouseRowShelfEdit from './containers/warehouse/warehouseRowShelfEdit'
import WarehouseLibrary from './containers/warehouse/warehouseLibrary'
import WarehouseLibraryAdd from './containers/warehouse/warehouseLibraryAdd'
import WarehouseLibraryEdit from './containers/warehouse/warehouseLibraryEdit'
import CustomerDemandOrder from './containers/customer/customerDemandOrder'
import CustomerDemandOrderAdd from './containers/customer/customerDemandOrderAdd'
import CustomerDemandOrderEdit from './containers/customer/customerDemandOrderEdit'
import CustomerProgrammeOrder from './containers/customer/customerProgrammeOrder'
import CustomerProgrammeOrderInfo from './containers/customer/customerProgrammeOrderInfo'
import CustomerProgrammeAdd from './containers/customer/customerProgrammeAdd'
import CustomerProgrammeEdit from './containers/customer/customerProgrammeEdit'
export default class App extends Component {
  render() {
    return (
      <BrowserRouter>
        <Switch>
          <Route path="/" exact component={Login} />
          <Index>
            <Switch>
              <Route path='/home' component={Home} breadcrumbName="首页" name="home" />
              <Route path="/goods" component={Goods} />
              <Route path="/goodsBindSupplier/:id" component={GoodsBindSupplier} />
              <Route path="/goodsType" component={GoodsType} />
              <Route path="/goodsTypeAdd" component={GoodsTypeAdd} />
              <Route path="/goodsTypeAddChild/:id" component={GoodsTypeAddChild} />
              <Route path="/goodsTypeEdit/:id" component={GoodsTypeEdit} />
              <Route path="/proExclude" component={ProExclude} />
              <Route path="/proExcludeAdd" component={ProExcludeAdd} />
              <Route path="/proExcludeEdit/:id/:product_num" component={ProExcludeEdit} />
              <Route path="/proFruit" component={ProFruit} />
              <Route path="/proFruitAdd" component={ProFruitAdd} />
              <Route path="/proFruitEdit/:id/" component={ProFruitEdit} />
              <Route path="/proOffline" component={ProOffline} />
              <Route path="/proOfflineFirst" component={ProOffline} />
              <Route path="/proOfflineSecond" component={ProOffline} />
              <Route path="/proOfflineThird" component={ProOffline} />
              <Route path="/vendor" component={Vendor} />
              <Route path="/vendorAdd" component={VendorAdd} />
              <Route path="/vendorEdit/:id" component={VendorEdit} />
              <Route path="/vendorBindProduct/:id" component={VendorBindProduct} />
              <Route path="/warehouse" component={Warehouse} />
              <Route path="/warehouseAdd" component={WarehouseAdd} />
              <Route path="/warehouseEdit/:id" component={WarehouseEdit} />
              <Route path="/warehouseArea" component={WarehouseArea} />
              <Route path="/warehouseAreaAdd" component={WarehouseAreaAdd} />
              <Route path="/warehouseAreaEdit/:id" component={WarehouseAreaEdit} />
              <Route path="/warehouseRowShelf" component={WarehouseRowShelf} />
              <Route path="/warehouseRowShelfAdd" component={WarehouseRowShelfAdd} />
              <Route path="/warehouseRowShelfEdit/:id" component={WarehouseRowShelfEdit} />
              <Route path="/warehouseLibrary" component={WarehouseLibrary} />
              <Route path="/warehouseLibraryAdd" component={WarehouseLibraryAdd} />
              <Route path="/warehouseLibraryEdit/:id" component={WarehouseLibraryEdit} />
              <Route path="/customerDemandOrder" component={CustomerDemandOrder} />
              <Route path="/customerDemandOrderAdd" component={CustomerDemandOrderAdd} />
              <Route path="/customerDemandOrderEdit/:id" component={CustomerDemandOrderEdit} />
              <Route path="/customerProgrammeOrder" component={CustomerProgrammeOrder} />
              <Route path="/customerProgrammeOrderInfo/:id" component={CustomerProgrammeOrderInfo} />
              <Route path="/customerProgrammeEdit/:id/:select_fa" component={CustomerProgrammeEdit} />
              <Route path="/customerProgrammeAdd/:id" component={CustomerProgrammeAdd} />
              <Route component={NoMatch} />
            </Switch>
          </Index>
        </Switch>
      </BrowserRouter>
    );
  }
}