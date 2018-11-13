import React, { Component } from 'react';
import { Route, Switch, BrowserRouter } from 'react-router-dom'
import { Provider } from 'react-redux';
import store from './reduxs/store';
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
export default class App extends Component {
  render() {
    return (
      <Provider store={store}>
        <BrowserRouter>
          <Switch>
            <Route path="/" exact component={Login} />
            <Index>
              <Switch>
                <Route path='/home' component={Home} />
                <Route path="/goods" component={Goods} />
                <Route path="/goodsBindSupplier/:id" component={GoodsBindSupplier} />
                <Route path="/goodsType" component={GoodsType} />
                <Route path="/goodsTypeAdd" component={GoodsTypeAdd} />
                <Route path="/goodsTypeAddChild/:id" component={GoodsTypeAddChild} />
                <Route path="/goodsTypeEdit/:id" component={GoodsTypeEdit} />
                <Route path="/proExclude" component={ProExclude} />
                <Route path="/proExcludeAdd" component={ProExcludeAdd} />
                <Route path="/proExcludeEdit/:id/:pro_code" component={ProExcludeEdit} />
                <Route path="/proFruit" component={ProFruit} />
                <Route path="/proFruitAdd" component={ProFruitAdd} />
                <Route path="/proFruitEdit/:id/" component={ProFruitEdit} />
                <Route path="/proOffline" component={ProOffline} />
                <Route path="/proOfflineFirst" component={ProOffline} />
                <Route path="/proOfflineSecond" component={ProOffline} />
                <Route path="/proOfflineThird" component={ProOffline} />
                <Route component={NoMatch} />
              </Switch>
            </Index>
          </Switch>
        </BrowserRouter>
      </Provider >
    );
  }
}