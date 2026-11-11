@props(['category' => null, 'menu' => null, 'count' => 0])
<span class="yza-menu-unread" @if($category) data-notification-category="{{ $category }}" @else data-notification-menu="{{ $menu }}" @endif
      @if((int) $count < 1) hidden @endif role="img" aria-label="{{ (int) $count }} notifikasi belum dibaca" title="{{ (int) $count }} notifikasi belum dibaca"></span>
