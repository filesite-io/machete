<?php
/**
 * Ta荐的核心指标
 */
Class TajianStats {
	//注册用户总数
	static $total_user = 0;
	//收藏的视频总数
	static $total_video = 0;
	//添加的分类总数
	static $total_tag = 0;

	//上一次保存到缓存文件的时间
	static $cache_save_time = 0;

	static $cache_filename = 'tajian_stats.json';

	//初始化数据，优先从本地缓存文件恢复数据
	public static function init() {
		$filepath = __DIR__ . '/../runtime/' . self::$cache_filename;

		try {
			if (file_exists($filepath)) {
				$json = file_get_contents($filepath);
				$stats = json_decode($json, true);
				self::$total_user = $stats['user'];
				self::$total_video = $stats['video'];
				self::$total_tag = $stats['tag'];
				self::$cache_save_time = $stats['cache_time'];
			}
		}catch(Exception $e) {
			//文件读取异常
		}

		return self::get();
	}

	//保存到本地文件，规则：距离上一次保存时间至少间隔10分钟
	public static function save() {
		$saved = false;
		$filepath = __DIR__ . '/../runtime/' . self::$cache_filename;

		try {
			self::$cache_save_time = time();	//记录更新时间
			$stats = self::get();
			$saved = file_put_contents($filepath, json_encode($stats));
		}catch(Exception $e) {
			//文件写入异常
		}

		return $saved !== false;
	}

	//返回统计数据
	public static function get() {
		return array(
			'user' => self::$total_user,
			'video' => self::$total_video,
			'tag' => self::$total_tag,
			'cache_time' => self::$cache_save_time,
		);
	}

	public static function increase($data_type) {
		$total = 0;

		switch($data_type) {
			case 'user':
				self::$total_user ++;
				$total = self::$total_user;
				break;

			case 'video':
				self::$total_video ++;
				$total = self::$total_video;
				break;

			case 'tag':
				self::$total_tag ++;
				$total = self::$total_tag;
				break;
		}

		return $total;
	}

	public static function decrease($data_type) {
		$total = 0;

		switch($data_type) {
			case 'video':
				self::$total_video --;
				$total = self::$total_video;
				break;

			case 'tag':
				self::$total_tag --;
				$total = self::$total_tag;
				break;
		}

		return $total;
	}
}