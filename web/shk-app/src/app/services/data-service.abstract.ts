import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';

import { Observable } from 'rxjs/Observable';
import { of } from 'rxjs/observable/of';
import { catchError, map, tap } from 'rxjs/operators';

import { QueryOptions } from '../models/query-options';
import { DataList } from '../models/data-list.interface';
import { SimpleEntity } from '../models/simple-entity.interface';

export interface outputData {
    data: any | any[] | null;
    successMsg: string;
    errorMsg: string;
    total: number;
}

export abstract class DataService<M extends SimpleEntity> {

    public headers = new HttpHeaders({'Content-Type': 'application/json'});
    private requestUrl = '';

    constructor(
        public http: HttpClient
    ) {
        this.requestUrl = 'app/data_list';
    }

    setRequestUrl(url){
        this.requestUrl = url;
    }

    getRequestUrl(){
        return this.requestUrl;
    }

    getItem(id: number): Observable<M> {
        const url = this.getRequestUrl() + `/${id}`;
        return this.http.get<M>(url).pipe(
            catchError(this.handleError<M>())
        );
    }

    getList(options ?: QueryOptions): Observable<M[]> {
        let params = new HttpParams();
        for(let name in options){
            if(!options.hasOwnProperty(name)){
                continue;
            }
            params = params.append(name, options[name]);
        }
        return this.http.get<M[]>(this.getRequestUrl(), {params: params})
            .pipe(
                catchError(this.handleError())
            );
    }

    getListPage(options ?: QueryOptions): Observable<DataList<M>> {
        let params = new HttpParams();
        for(let name in options){
            if(!options.hasOwnProperty(name)){
                continue;
            }
            params = params.append(name, options[name]);
        }
        return this.http.get<DataList<M>>(this.getRequestUrl(), {params: params})
            .pipe(
                catchError(this.handleError())
            );
    }

    deleteItem(id: number): Observable<M> {
        const url = this.getRequestUrl() + `/${id}`;
        return this.http.delete<M>(url, {headers: this.headers}).pipe(
            catchError(this.handleError<M>())
        );
    }

    deleteByArray(idsArray: number[]): Observable<M> {
        const url = this.getRequestUrl() + '/batch';
        let params = new HttpParams();
        params.set('ids', JSON.stringify(idsArray));
        return this.http.delete(url, {
                headers: this.headers,
                params: params
            })
            .pipe(
                catchError(this.handleError<M>())
            );
    }

    create(item: M): Observable<M> {
        return this.http.post<M>(this.getRequestUrl(), item, {headers: this.headers}).pipe(
            catchError(this.handleError<M>())
        );
    }

    update(item: M): Observable<M> {
        const url = this.getRequestUrl() + `/${item.id}`;
        return this.http.put(url, item, {headers: this.headers}).pipe(
            catchError(this.handleError<M>())
        );
    }

    handleError<T> (operation = 'operation', result?: T) {
        return (err: any): Observable<T> => {
            if (err.error) {
                throw err.error;
            }

            return of(result as T);
        };
    }
}
