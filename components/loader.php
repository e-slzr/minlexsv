<div class="loader-container active">
    <div class="loader"></div>
    <div class="loader-text">Cargando...</div>
</div>

<script>
    $(document).ready(function() {
        $('body').addClass('loading');
        $(window).on('load', function() {
            $('.loader-container').removeClass('active').fadeOut('slow', function() {
                $('body').removeClass('loading');
            });
        });
    });
</script>
