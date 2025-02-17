<div class="modal fade bd-example-modal-lg" id="notifications-board" tabindex="-1" role="dialog"
     aria-hidden="true" aria-labelledby="notifications-board-title">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-4" id="notifications-board-title">
                    {{ __('notifications.title') }}
                </h2>
                <a href="javascript:void(0)" class="text-muted" id="mark-all-read"
                        aria-label="{{ __('notifications.mark-all-read') }}">
                    <span aria-hidden="true"><i class="fa-solid fa-check-double"></i></span>
                </a>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notifications-list">
                <div id="notifications-empty" class="text-center text-muted">
                    {{ __('notifications.empty') }}
                    <br/>¯\_(ツ)_/¯
                </div>
            </div>
        </div>
    </div>
</div>
