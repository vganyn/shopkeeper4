import {NgModule} from '@angular/core';
import {NoPreloading, Router, RouterModule, Routes} from '@angular/router';
import {NotFoundComponent} from "./not-found.component";

export const routes: Routes = [
    {path: '', redirectTo: 'orders', pathMatch: 'full'},
    {path: 'orders', loadChildren: './orders/orders.module#OrdersModule'},
    {path: 'catalog', loadChildren: './catalog/catalog.module#CatalogModule'},
    {path: 'statistics', loadChildren: './statistics/statistics.module#StatisticsModule'},
    {path: 'users', loadChildren: './users/users.module#UsersModule'},
    {path: 'settings', loadChildren: './settings/settings.module#SettingsModule'},
    {path: '**', component: NotFoundComponent}
];

@NgModule({
    imports: [RouterModule.forRoot(routes, {
        useHash: true,
        preloadingStrategy: NoPreloading
    })],
    exports: [RouterModule]
})
export class AppRoutingModule {

}
