window.initTrackingMap = function(payload, token, pollSeconds){
  return window.MovaMap?.drawMap('tracking-map', payload, { liveUrl: `/track/${token}/live`, pollSeconds });
};
