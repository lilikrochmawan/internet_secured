<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  3.0.0   |
    |              on 2026-06-24 23:14:50              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
 namespace M71Jr\xz1TU\aa3PK; use Closure; use MVYyO\Xz1tU\OhhId; use MVyyO\btKlJ\lP8pF\YZyUB; use okL8W\ZEW93\DlSwm\atAmr; class ClientMiddleware { public function oMDek(Request $den9F, Closure $PLrlI): Response { goto pQfMZ; pQfMZ: if (!Auth::SXvuB()) { goto Hg47C; } goto eh7rt; yi5Wb: return redirect()->gxr_2("\x61\144\155\151\156\x2e\x64\x61\x73\x68\x62\157\141\x72\x64"); goto bnMyZ; eh7rt: $LjaJw = Auth::WZ2VM(); goto RXCC3; yHuil: if ($LjaJw->UJoNp) { goto otgh0; } goto vyw1W; bnMyZ: GhQPH: goto yHuil; Fs2_r: return $PLrlI($den9F); goto zVwrd; Naidb: otgh0: goto zIBJb; YoTnp: return redirect()->gXr_2("\154\157\147\x69\x6e")->aBL7m(["\x70\150\x6f\156\x65" => "\101\x6b\x75\x6e\40\160\x65\154\141\x6e\147\147\141\156\40\101\x6e\x64\141\40\164\x69\x64\x61\x6b\x20\166\x61\154\x69\144\40\141\164\141\x75\x20\164\x69\x64\x61\x6b\40\144\x69\x74\145\x6d\x75\x6b\x61\x6e\x2e"]); goto Naidb; zIBJb: Hg47C: goto Fs2_r; RXCC3: if (!$LjaJw->vKDY1()) { goto GhQPH; } goto yi5Wb; vyw1W: Auth::DsMZd(); goto YoTnp; zVwrd: } }
