import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgbModal, NgbActiveModal, NgbModalRef, NgbTooltipConfig } from '@ng-bootstrap/ng-bootstrap';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { ContentType } from './models/content_type.model';
import { Category } from "./models/category.model";
import { ConfirmModalContent } from './app.component';
import { ListRecursiveComponent } from './list-recursive.component';
import { ModalContentAbstractComponent } from './page-table.abstract';
import 'rxjs/add/operator/switchMap';
import * as _ from "lodash";

import { SystemNameService } from './services/system-name.service';
import { CategoriesService } from './services/categories.service'
import { ContentTypesService } from './services/content_types.service';
import { Observable } from 'rxjs/Observable';

/**
 * @class CategoriesModalComponent
 */
@Component({
    selector: 'category-modal-content',
    templateUrl: 'templates/modal-category.html',
    providers: [CategoriesService, SystemNameService, ContentTypesService]
})
export class CategoriesModalComponent extends ModalContentAbstractComponent {

    @Input() categories: Category[] = [];
    @Input() currentCategory: Category;
    @Input() isRoot: boolean = false;
    model: Category = new Category(null, false, 0, '', '', '', '', true);
    contentTypes: ContentType[] = [];

    formFields = {
        title: {
            value: '',
            disabled: false,
            validators: [Validators.required],
            messages: {
                required: 'Title is required.'
            }
        },
        name: {
            value: '',
            disabled: false,
            validators: [Validators.required],
            messages: {
                required: 'Name is required.'
            }
        },
        description: {
            value: '',
            disabled: false,
            validators: [],
            messages: {}
        },
        parentId: {
            value: 0,
            disabled: false,
            validators: [],
            messages: {}
        },
        contentTypeName: {
            value: '',
            disabled: false,
            validators: [Validators.required],
            messages: {
                required: 'Content type is required.'
            }
        },
        isActive: {
            value: false,
            disabled: false,
            validators: [],
            messages: {}
        }
    };

    constructor(
        public fb: FormBuilder,
        public dataService: CategoriesService,
        public systemNameService: SystemNameService,
        public activeModal: NgbActiveModal,
        public tooltipConfig: NgbTooltipConfig,
        private contentTypesService: ContentTypesService
    ) {
        super(fb, dataService, systemNameService, activeModal, tooltipConfig);
    }

    /** On initialize */
    ngOnInit(): void {

        // Filter root category
        const rootIndex = _.findIndex(this.categories, {name: 'root'});
        if (rootIndex > -1) {
            this.categories.splice(rootIndex, 1);
        }

        this.model.parentId = this.currentCategory.id;
        this.model.contentTypeName = this.currentCategory.contentTypeName;
        if (this.isRoot) {
            this.model.id = 0;
            this.model.title = 'Корневая категория';
            this.model.name = 'root';
            this.formFields.title.disabled = true;
            this.formFields.name.disabled = true;
            this.formFields.isActive.disabled = true;
        }
        ModalContentAbstractComponent.prototype.ngOnInit.call(this);
        this.getContentTypes();
    }

    getContentTypes() {
        this.contentTypesService.getList()
            .subscribe(
                preparedData => {
                    this.contentTypes = preparedData.data;
                    this.errorMessage = preparedData.errorMsg;
                },
                error => this.errorMessage = <any>error);
    }

    save(): void {
        this.submitted = true;

        if (!this.form.valid) {
            this.onValueChanged('form');
            this.submitted = false;
            return;
        }

        let callback = function (res: any) {
            if (res.success) {
                this.closeModal();
            } else {
                if (res.msg) {
                    this.submitted = false;
                    this.errorMessage = res.msg;
                }
            }
        };

        if (this.isEditMode) {
            this.dataService.update(this.model).then(callback.bind(this));
        } else {
            this.dataService.create(this.model).then(callback.bind(this));
        }
    }
}

/**
 * @class CategoriesListComponent
 */
@Component({
    selector: 'categories-list',
    template: `
        <ul class="dropdown-menu dropdown-menu-hover" *ngIf="items.length > 0" [class.shadow]="parentId != 0">
            <li class="dropdown-item active" *ngFor="let item of items" [class.active]="item.id == currentId"
                [class.current-level]="getIsActiveParent(item.id)">
                <i class="icon-keyboard_arrow_right float-right m-2 pt-1" [hidden]="!item.isFolder"></i>
                <a href="" [routerLink]="['/catalog/category/', item.id]" [class.text-muted]="!item.isActive">
                    {{item.title}}
                </a>
                <categories-list [inputItems]="inputItems" [parentId]="item.id" [currentId]="currentId"></categories-list>
            </li>
        </ul>
    `
})
export class CategoriesListComponent extends ListRecursiveComponent {

}

/**
 * @class CategoriesMenuComponent
 */
@Component({
    selector: 'categories-menu',
    templateUrl: 'templates/categories-menu.html',
    providers: [CategoriesService]
})
export class CategoriesMenuComponent implements OnInit {
    @Input() rootTitle: string = 'Категории';
    @Output() changeRequest = new EventEmitter<Category>();
    currentCategory: Category = new Category(null, false, 0, 'root', this.rootTitle, '', '', true);
    categories: Category[] = [];
    errorMessage: string = '';
    modalRef: NgbModalRef;
    categoryId: number = 0;

    constructor(
        public router: Router,
        private route: ActivatedRoute,
        private modalService: NgbModal,
        private categoriesService: CategoriesService
    ) {
    }

    /** On initialize component */
    ngOnInit(): void {
        this.getCategoriesRequest()
            .subscribe(
                preparedData => {
                    this.categories = _.clone(preparedData.data);

                    this.route.paramMap
                        .subscribe(
                            params => {
                                this.categoryId = params.get('categoryId')
                                    ? parseInt(params.get('categoryId'))
                                    : 0;
                                this.selectCurrent();
                            }
                        );
                },
                error => this.errorMessage = <any>error
            );
    }

    /** Select current category */
    selectCurrent(): void {
        if (this.currentCategory.id === this.categoryId) {
            return;
        }
        const index = _.findIndex(this.categories, {id: this.categoryId});
        if(index > -1){
            this.currentCategory = _.clone(this.categories[index]);
            this.changeRequest.emit(this.currentCategory);
        } else {
            this.openRootCategory();
        }
    }

    /** Get categories */
    getCategoriesRequest(): Observable<any> {
        return this.categoriesService.getList();
    }

    /** Get categories */
    getCategories(): void {
        this.getCategoriesRequest()
            .subscribe(
                preparedData => {
                    this.categories = _.clone(preparedData.data);
                    this.selectCurrent();
                },
                error => this.errorMessage = <any>error
            );
    }

    /**
     * Open modal category
     * @param itemId
     * @param isItemCopy
     */
    openModalCategory(itemId?: number, isItemCopy: boolean = false): void {
        const isRoot = itemId === 0 || itemId === null;
        const isEditMode = typeof itemId !== 'undefined' && !isItemCopy;
        this.modalRef = this.modalService.open(CategoriesModalComponent, {size: 'lg'});
        this.modalRef.componentInstance.modalTitle = isEditMode ? 'Edit category' : 'Add category';
        this.modalRef.componentInstance.itemId = itemId || 0;
        this.modalRef.componentInstance.isItemCopy = isItemCopy || false;
        this.modalRef.componentInstance.categories = _.cloneDeep(this.categories);
        this.modalRef.componentInstance.currentCategory = this.currentCategory;
        this.modalRef.componentInstance.isRoot = isRoot;
        this.modalRef.componentInstance.isEditMode = isEditMode;
        this.modalRef.result.then((result) => {
            this.currentCategory.id = null;// For update current category data
            this.getCategories();
        }, (reason) => {

        });
    }

    /**
     * Update category data
     * @param itemId
     * @param data
     */
    updateCategoryData(itemId: number, data: any): void {
        let index = _.findIndex(this.categories, {id: itemId});
        if (index === -1) {
            return;
        }
        let category = this.categories[index];
        if (category.parentId == data.parentId) {
            Object.keys(category).forEach(function (k, i) {
                if (data[k]) {
                    category[k] = data[k];
                }
            });
        } else {
            this.getCategories();
        }
    }

    /**
     * Confirm delete category
     * @param itemId
     */
    deleteCategoryItemConfirm(itemId: number): void {
        let index = _.findIndex(this.categories, {id: itemId});
        if (index == -1) {
            return;
        }
        this.modalRef = this.modalService.open(ConfirmModalContent);
        this.modalRef.componentInstance.modalTitle = 'Confirm';
        this.modalRef.componentInstance.modalContent = 'Are you sure you want to remove category "' + this.categories[index].title + '"?';
        this.modalRef.result.then((result) => {
            if (result == 'accept') {
                this.deleteCategoryItem(itemId);
            }
        }, (reason) => {

        });
    }

    /**
     * Delete category
     * @param itemId
     */
    deleteCategoryItem(itemId: number): void {
        this.categoriesService.deleteItem(itemId)
            .then((res) => {
                if (res.success) {
                    this.categoryId = 0;
                    this.selectCurrent();
                    this.getCategories();
                } else {
                    if (res.msg) {
                        this.errorMessage = res.msg;
                    }
                }
            });
    }

    /** Open root category */
    openRootCategory(): void {
        this.currentCategory = new Category(null, false, 0, 'root', this.rootTitle, '', '', true);
        this.changeRequest.emit(this.currentCategory);
    }

    /** Go to root category */
    goToRootCategory(): void {
        this.router.navigate(['/catalog/category/0']);
    }

    /**
     * Select category
     * @param category
     */
    selectCategory(category: Category): void {
        this.router.navigate(['/catalog/category', category.id]);
    }

    /** Copy category */
    copyCategory(): void {
        this.openModalCategory(this.currentCategory.id, true);
    }
}

